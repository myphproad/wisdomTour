<?php
namespace app\admin\controller;

use think\Controller;
use think\Db;
class Database extends Common
{
    public static $path = "./data/backup/";
    public function index($type = null)
    {
        switch ($type) {
            case 'import':
                
                $path = realpath(self::$path);
                $flag = \FilesystemIterator::KEY_AS_FILENAME;
                $glob = new \FilesystemIterator($path,  $flag);
                $list = array();
                foreach ($glob as $name => $file) {
                    if(preg_match('/^\d{8,8}-\d{6,6}-\d+\.sql(?:\.gz)?$/', $name)){
                        $name = sscanf($name, '%4s%2s%2s-%2s%2s%2s-%d');

                        $date = "{$name[0]}-{$name[1]}-{$name[2]}";
                        $time = "{$name[3]}:{$name[4]}:{$name[5]}";
                        $part = $name[6];

                        if(isset($list["{$date} {$time}"])){
                            $info = $list["{$date} {$time}"];
                            $info['part'] = max($info['part'], $part);
                            $info['size'] = $info['size'] + $file->getSize();
                        } else {
                            $info['part'] = $part;
                            $info['size'] = $file->getSize();
                        }
                        $extension        = strtoupper(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
                        $info['compress'] = ($extension === 'SQL') ? '-' : $extension;
                        $info['time']     = strtotime("{$date} {$time}");

                        $list["{$date} {$time}"] = $info;
                    }
                }
                krsort($list);
                break;
            case 'export':
                $list = Db::query('SHOW TABLE STATUS');
                $list = array_map('array_change_key_case', $list);
                break;
            default:
                $this->error('参数错误！');
        }
        $this->assign('list', $list);
        return $this->fetch($type);
    }
    public function optimize()
    {
        if (isset($_POST['tables'])) {
            $tables = $_POST['tables'];
            $tables = implode('`,`', $tables);
            $list = Db::query("OPTIMIZE TABLE `{$tables}`");
            if ($list) {
                $this->success("数据表优化完成！");
            } else {
                $this->error("数据表优化出错请重试！");
            }
        } else {
            $this->error("请指定要优化的表！");
        }
    }
    public function repair()
    {
        if (isset($_POST['tables'])) {
            if (!isset($_POST['tables'])) {
                $this->error("请指定要修复的表！");
            }
            $tables = $_POST['tables'];
            $tables = implode('`,`', $tables);
            $list = Db::query("REPAIR TABLE `{$tables}`");
            if ($list) {
                $this->success("数据表修复完成！");
            } else {
                $this->error("数据表修复出错请重试！");
            }
        } else {
            $this->error("请指定要优化的表！");
        }
    }
    public function delete()
    {
        $time = intval($_GET['time']);
        $name = date('Ymd-His', $time) . '-*.sql*';
        $path = realpath(self::$path) . DIRECTORY_SEPARATOR . $name;
        array_map("unlink", glob($path));
        if (!count(glob($path))) {
            $this->success("删除成功！");
        } else {
            $this->error('删除失败');
        }
    }
    public function export()
    {
        if (isset($_POST['tables'])) {
            $tables = $_POST['tables'];
            dir_create(self::$path);
            $config = ['path' => realpath(self::$path) . DS,
			           'part' => '20971520',
					   'compress' => '1',
					   'level' => '9'];
            $lock = "{$config['path']}backup.lock";
            if (is_file($lock)) {
                $this->error('检测到有一个备份任务正在执行，请稍后再试！');
            } else {
                file_put_contents($lock, time());
            }
            is_writeable($config['path']) || $this->error('备份目录不存在或不可写，请检查后重试！');
            session('backup_config', $config);
            $file = ['name' => date('Ymd-His', time()), 'part' => 1];
            session('backup_file', $file);
            session('backup_tables', $tables);
            $Databack = new \org\Databack($file, $config);
            if (false !== $Databack->create()) {
                return $this->success('初始化成功！', url('Database/export', ['id' => 0, 'start' => 0]));
            } else {
                return $this->error('初始化失败，备份文件创建失败！');
            }
        } elseif (isset($_GET['id']) && isset($_GET['start'])) {
            $tables = session('backup_tables');
            $id = intval($_GET['id']);
            $start = intval($_GET['start']);
            $Databack = new \org\Databack(session('backup_file'), session('backup_config'));
            $r = $Databack->backup($tables[$id], $start);
            if (false === $r) {
                $this->error('备份出错！');
            } elseif (0 === $r) {
                if (isset($tables[++$id])) {
                    return $this->success($tables[$id - 1] . '备份完成！', url('Database/export', ['id' => $id, 'start' => 0]));
                } else {
                    @unlink(session('backup_config.path') . 'backup.lock');
                    session('backup_tables', null);
                    session('backup_file', null);
                    session('backup_config', null);
                    return $this->success('备份完成！', url('Database/index', ['type' => 'export']));
                }
            } else {
                $rate = floor(100 * ($r[0] / $r[1]));
                return $this->success($tables[$id] . "正在备份...({$rate}%)", url('Database/export', ['id' => $id, 'start' => $r[0]]));
            }
        } else {
            $this->error("请指定要备份的表！");
        }
    }
    public function import()
    {
        if (isset($_GET['time'])) {
            $time = intval($_GET['time']);
            $name = date('Ymd-His', $time) . '-*.sql*';
            $path = realpath(self::$path) . DIRECTORY_SEPARATOR . $name;
            $files = glob($path);
            $list = [];
            foreach ($files as $name) {
                $basename = basename($name);
                $match = sscanf($basename, '%4s%2s%2s-%2s%2s%2s-%d');
                $gz = preg_match('/^\\d{8,8}-\\d{6,6}-\\d+\\.sql.gz$/', $basename);
                $list[$match[6]] = [$match[6], $name, $gz];
            }
            ksort($list);
            $last = end($list);
            if (count($list) === $last[0]) {
                session('backup_list', $list);
                return $this->success('正在还原...！', url('Database/import', ['part' => 1, 'start' => 0]));
            } else {
                return $this->error('备份文件可能已经损坏，请检查！');
            }
        } elseif (isset($_GET['part']) && isset($_GET['start'])) {
            $part = intval($_GET['part']);
            $start = intval($_GET['start']);
            $list = session('backup_list');
            $db = new \org\Databack($list[$part], ['path' => realpath(self::$path), 'compress' => $list[$part][2]]);
            $r = $db->import($start);
            if (false === $r) {
                $this->error('还原数据出错！');
            } elseif (0 === $r) {
                if (isset($list[++$part])) {
                    return $this->success("正在还原...#{$part}", url('Database/import', ['part' => $part, 'start' => 0]));
                } else {
                    session('backup_list', null);
                    return $this->success('还原完成！', url('Database/index', ['type' => 'import']));
                }
            } else {
				
                if ($r[1]) {
                    $rate = floor(100 * ($r[0] / $r[1]));
                    return $this->success("正在还原...#{$part} ({$rate}%)", url('Database/import', ['part' => $part, 'start' => $r[0]]));
                } else {
                    return $this->success("正在还原...#{$part}", url('Database/import', ['part' => $part, 'start' => $r[0]]));
                }
            }
        } else {
            $this->error('参数错误！');
        }
    }
    
}
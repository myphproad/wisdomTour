/*全局定义*/
var HOST='.'; //  ,http://localhost/
console.log(HOST);
/*
 localStorage.getItem(key):获取指定key本地存储的值
 localStorage.setItem(key,value)：将value存储到key字段
 localStorage.removeItem(key):删除指定key本地存储的值
 */
/*定义方法*/
var setTools={
    /*设置桌面*/
    update_desk_wallpaper:function(img_src){
        wallpaperSrc= localStorage.getItem('wallpaperSrc'); //获取指定key本地存储的值
       console.log(wallpaperSrc);
        if(wallpaperSrc==''||wallpaperSrc==null){
           if(img_src==''||img_src==undefined){
              //第一次 默认页面
           }else{
              console.log('3');
              //没有存续img_src
              $(".desk-wallpaper").attr('src','');
              $(".desk-wallpaper-image-back").attr('src','');
              $(".desk-wallpaper-image").attr('src',img_src);
              $(".desk-wallpaper-image-back").attr('src',img_src);
           }
        }else{
              console.log('2');
              //已经存在图片缓存
              $(".desk-wallpaper").attr('src', '');
              $(".desk-wallpaper-image-back").attr('src', '');
              $(".desk-wallpaper-image").attr('src', wallpaperSrc);
              $(".desk-wallpaper-image-back").attr('src', wallpaperSrc);
        }
    },
   /*反馈信息*/
   play_feedback_message:function(){
      $('#feedback').css('display','inline');
   },
   /*关于我们*/
   play_about_message:function(){
      $('#copyright').css('display','inline');
   },
   index_list:function(tag,url){
      console.log(tag);
      if(tag==null||tag==''){
         console.log("1");
         window.location.href=url;
      }else{
         console.log("2");
        if(tag=='email'){
           setTools.play_feedback_message();
        }else if(tag=='wheather'){
           console.log("3");
             return true;
         }
      }
   }
};

$(function(){
   /*页面第一次加载*/
    setTools.update_desk_wallpaper();//更新桌面



   var APINAME = {
      getAddBanner: "ad_index", //首页轮播图
      getAllPproducts: "recom_goods_list" //首页商品列表
   };
   var API = {
      getAddBanner: HOST + "mobile.php?c=searchlist&a=ad_index", //首页轮播图
      getAllPproducts: HOST + "mobile.php?c=searchlist&a=recom_goods_list", //首页商品列表
   };
   /*获取当前时间*/
   var mydate = new Date();
   var year=  mydate.getFullYear(); //获取完整的年份(4位,1970-????)
   var  month=mydate.getMonth(); //获取当前月份(0-11,0代表1月)
   var date=mydate.getDate(); //获取当前日(1-31)
   var t=mydate.toLocaleTimeString();//当前小时 分钟
   $(".footer-banner-action-time").prop('data-original-title',year+month+date);
   $(".footer-banner-action-time").text(t);

   // this is for the appbar-demo page
   if ($("#appbar-theme-select").length){
      $("#appbar-theme-select").change(function(){
         var ui = $(this).val();

         if (ui != '')
            $("footer.win-commandlayout")
               .removeClass("win-ui-light win-ui-dark")
               .addClass(ui);
      });
   }


   //样式切换
   if ($("#win-theme-select").length){
      $("#win-theme-select").change(function(){
         var css = $(this).val();

         if (css != '')
            updateCSS(css);
      });
   }

/*个性化设置*/
   $("#settings").click(function (e) {
      e.preventDefault();
      $('#charms').charms('showSection', 'theme-charms-section');
   });


   $(".custome-messager-btn").click(function (e) {
      var data_key=$(this).attr('data-key');
      if(data_key=='yes'){
         //确定发送
         $('#feedback').css('display','none');
         return false;
      }
      if(data_key=='no'){
         //关闭
         $('#feedback').css('display','none');
         return false;
      }
      $('#feedback').css('display','inline');
   });
    /*切换主题*/
    $('.set-theme-item').click(function(){
        css_name=$(this).attr('data-theme');
        updateCSS(css_name);
    })
    /*切换桌面*/
    $('.wallpaper-list').click(function(event){
       //localStorage.removeItem('wallpaperSrc');
       img_src=$(this).find('img').attr('src');
       console.log(img_src);
       localStorage.setItem('wallpaperSrc',img_src);//将value存储到key字段
       setTools.update_desk_wallpaper(img_src);
    })

   $(".custome-messager-btn").click(function (e) {
         $('#copyright').css('display','none');
   });
   //发送反馈信息
   function saveFeedback(values){
      $.ajax({
         url: ctx + "/feedback/save",
         method: "POST",
         data: values,
         success: function(data) {
            if (data.success) {
               HteOS.Messager.alert("感谢","非常感谢您能给我们提供的宝贵意见，您的反馈建议我们将会认真的评估。")
            }
         }
      });
   }
   // listview demo
   $('#listview-grid-demo').on('click', '.mediumListIconTextItem', function(e){
      e.preventDefault();
      $(this).toggleClass('selected');
   });


   //$('#home-carousel').carousel({interval: 5000});

});


//更新css样式 文件
function updateCSS(css){

   $("head").append('<link rel="stylesheet" type="text/css" href="'+HOST +'/public/2017/css/' + css +'.css">');
/*判断删除第一个*/
   if($("link[href*=metro-ui-]").size() > 1){
      $("link[href*=metro-ui-]:first").remove();
   }
   
};



// NOTICE!! DO NOT USE ANY OF THIS JAVASCRIPT
// IT'S ALL JUST JUNK FOR OUR DOCS!
// ++++++++++++++++++++++++++++++++++++++++++

!function ($) {

   $(function(){

      var $window = $(window)

      // Disable certain links in docs
      $('section [href^=#]').click(function (e) {
         e.preventDefault()
      })

      // side bar
      $('.bs-docs-sidenav').affix({
         offset: {
            top: function () { return $window.width() <= 980 ? 290 : 210 }
            , bottom: 270
         }
      })

      // make code pretty
      window.prettyPrint && prettyPrint()

      // add-ons
      $('.add-on :checkbox').on('click', function () {
         var $this = $(this)
            , method = $this.attr('checked') ? 'addClass' : 'removeClass'
         $(this).parents('.add-on')[method]('active')
      })

      // add tipsies to grid for scaffolding
      if ($('#gridSystem').length) {
         $('#gridSystem').tooltip({
            selector: '.show-grid > div'
            , title: function () { return $(this).width() + 'px' }
         })
      }

      // tooltip demo
      $('.footer-banner').tooltip({
         selector: ".footer-banner-action[rel=tooltip]"
      })

      $('.tooltip-test').tooltip()
      $('.popover-test').popover()

      // popover demo
      $("a[rel=popover]")
         .popover()
         .click(function(e) {
            e.preventDefault()
         })

      // button state demo
      $('#fat-btn')
         .click(function () {
            var btn = $(this)
            btn.button('loading')
            setTimeout(function () {
               btn.button('reset')
            }, 3000)
         })

      // carousel demo
      $('#myCarousel').carousel()

      // javascript build logic
      var inputsComponent = $("#components.download input")
         , inputsPlugin = $("#plugins.download input")
         , inputsVariables = $("#variables.download input")

      // toggle all plugin checkboxes
      $('#components.download .toggle-all').on('click', function (e) {
         e.preventDefault()
         inputsComponent.attr('checked', !inputsComponent.is(':checked'))
      })

      $('#plugins.download .toggle-all').on('click', function (e) {
         e.preventDefault()
         inputsPlugin.attr('checked', !inputsPlugin.is(':checked'))
      })

      $('#variables.download .toggle-all').on('click', function (e) {
         e.preventDefault()
         inputsVariables.val('')
      })

      // request built javascript
      $('.download-btn').on('click', function () {
         var css = $("#components.download input:checked")
               .map(function () { return this.value })
               .toArray()
            , js = $("#plugins.download input:checked")
               .map(function () { return this.value })
               .toArray()
            , vars = {}
            , img = ['glyphicons-halflings.png', 'glyphicons-halflings-white.png']

         $("#variables.download input")
            .each(function () {
               $(this).val() && (vars[ $(this).prev().text() ] = $(this).val())
            })

         $.ajax({
            type: 'POST'
            , url: /\?dev/.test(window.location) ? 'http://localhost:3000' : 'http://bootstrap.herokuapp.com'
            , dataType: 'jsonpi'
            , params: {
               js: js
               , css: css
               , vars: vars
               , img: img
            }
         })
      })
   })

   // Modified from the original jsonpi https://github.com/benvinegar/jquery-jsonpi
   $.ajaxTransport('jsonpi', function(opts, originalOptions, jqXHR) {
      var url = opts.url;
      return {
         send: function(_, completeCallback) {
            var name = 'jQuery_iframe_' + jQuery.now()
               , iframe, form

            iframe = $('<iframe>')
               .attr('name', name)
               .appendTo('head')

            form = $('<form>')
               .attr('method', opts.type) // GET or POST
               .attr('action', url)
               .attr('target', name)

            $.each(opts.params, function(k, v) {

               $('<input>')
                  .attr('type', 'hidden')
                  .attr('name', k)
                  .attr('value', typeof v == 'string' ? v : JSON.stringify(v))
                  .appendTo(form)
            })

            form.appendTo('body').submit()
         }
      }
   })

}(window.jQuery)
/*

 //设置用户信息
 HteOS.User = {
 id : "",
 name : "",
 face : "",
 wallpaper :  ("" || "images/wallpaper/11.jpg"),
 logon : ("".length > 0)
 }
 HteOS.apply(HteOS.Settings,{
 wallpaper : ("" || "images/wallpaper.jpg"),
 shortcutSize : ("" || "small"),
 mode :  ("" || "" || "metro")
 });
 if(HteOS.Settings.mode == 'desktop'){
 var containerContextMenu = HteOS.desktop.ContextMenu.containerContextMenu;
 containerContextMenu.insert({
 id :'switch',
 icon : 'glyphicon glyphicon-th-large',
 name :'桌面样式',
 items : [{
 id : 'metro',
 name : '磁贴',
 handler : function(){
 switchMode('metro');
 }
 },{
 id : 'metro',
 name : '图标',
 icon : 'glyphicon glyphicon-ok'
 }]
 },1);
 }else{
 var containerContextMenu = HteOS.metro.ContextMenu.containerContextMenu;
 containerContextMenu.insert({
 id :'switch',
 icon : 'glyphicon glyphicon-th-large',
 name :'桌面样式',
 items : [{
 id : 'metro',
 name : '磁贴',
 icon : 'glyphicon glyphicon-ok'
 },{
 id : 'metro',
 name : '图标',
 handler : function(){
 switchMode('desktop');
 }
 }]
 },0);
 }

 HteOS.EventManager.on("hte.dockbar.init",function(){
 var name = "图标模式"
 if(HteOS.Settings.mode == 'desktop'){
 name = "磁贴模式";
 }
 var switcher = $("<li class=\"hte-dockbar-action hte-dockbar-action-profile\">"
 + "<span class=\"hte-dockbar-action-icon glyphicon glyphicon-th-large\"></span>"
 +"<div class=\"hte-dockbar-action-name\">"+name+"</div>"
 +"</li>");
 switcher.appendTo($(".hte-dockbar-content"));
 switcher.click(function(){
 if(HteOS.Settings.mode == 'desktop'){
 switchMode('metro');
 }else{
 switchMode('desktop');
 }
 });
 });

 HteOS.EventManager.on("hte.taskbar.init",function(){
 var example = {
 title : '示例',
 icon : 'glyphicon glyphicon-book',
 handler : function(){
 HteOS.TaskManager.start({
 id : 'exampleTask',
 name : '示例',
 icon : 'images/logo.png',
 path : 'examples/index.html',
 mode : 'window'
 });
 }
 };
 HteOS.Taskbar.addRightAction(example);
 var feedback = {
 title : '反馈意见',
 icon : 'glyphicon  glyphicon-edit',
 handler : function(){
 HteOS.Messager.prompt("意见反馈","我们迫切希望得到您有价值的反馈，有您的反馈，我们会做的更好！",[{
 name : 'email',
 label : '联系方式',
 value : "",
 inputType:"email"
 },{
 name : 'content',
 label : '反馈意见',
 multiLine : true
 }],function(btn,values){
 if(btn == 'yes'){
 if(values.content){
 saveFeedback(values);
 }
 }
 });
 }
 };
 HteOS.Taskbar.addRightAction(feedback);
 var about = {
 title : '关于HteOS',
 icon : 'glyphicon glyphicon-info-sign',
 handler :welcom
 };
 HteOS.Taskbar.addRightAction(about);
 });
 }

 function switchMode(mode){
 HteOS.Messager.showLoading("","正在切换中....")
 $.ajax({
 url : HteOS.API.SAVE_MODE,
 method : 'POST',
 data : {
 mode : mode
 }
 }).success(function(data){
 if(data.success){
 window.location.reload();
 }else{
 HteOS.Messager.hide();
 HteOS.Notification.show("", "应用消息",
 data.message, true)
 }
 }).error(function(){
 HteOS.Messager.hide();
 HteOS.Notification.show("", "应用消息",
 "切换模式失败", true)
 });
 }

 function afterstart(){

 welcom();
 };


 }
 });
 }*/
----------------------- priview image--------------------------
jQuery(document).ready(function($) {
        //exec src image preview
        var srcDemo = $('.img-upload > img').attr('src');
        srcDemo = srcDemo.replace('index.php/','');
        $('.img-upload > img').attr('src',srcDemo);

        var allowType = ['image/jpeg','image/png','image/gif'];
        var domInputFile = $("input[type=file]");
        function readURL(input) {
            if (input.files && input.files[0]) {
                var fileUpload = input.files[0];
                if($.inArray(fileUpload.type, allowType) < 0) {
                    $('.img-upload > img').attr('src', srcDemo);
                }
                else {
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        $('.img-upload > img').attr('src', e.target.result);
                    }
                    reader.readAsDataURL(fileUpload);
                }
            }
        }
        $("input[type=file]").change(function(){
            readURL(this);
        });
    });

----------------------- --------------------------

dbclick

<script>
    jQuery(document).ready(function($) {
        var clickedOnce = false;
        var timer;

        $("body").bind("click", function(){
            if (clickedOnce) {
                run_on_double_click();
            } else {
                timer = setTimeout(function() {
                    run_on_simple_click();
                }, 150);
                clickedOnce = true;
            }
        });

        function run_on_simple_click() {
            alert("simpleclick");
            clickedOnce = false;
        }

        function run_on_double_click() {
            clickedOnce = false;
            clearTimeout(timer);
            alert("doubleclick");
        }
    });
</script>
-------------------------------------
remove hover menu magento
/js/varien/menu.js
remove line: list.onmouseover = function(){
                    main.fireNavEvent(this,true);
                };
                list.onmouseout = function(){
                    main.fireNavEvent(this,false);
                };

--------------------------------------------------------------------------
outsite dom click find
$(document).mouseup(function (e)
{
    var container = $("YOUR CONTAINER SELECTOR");

    if (!container.is(e.target) // if the target of the click isn't the container...
        && container.has(e.target).length === 0) // ... nor a descendant of the container
    {
        container.hide();
    }
});

--------------------------------------------------------------------------
scrool banner
jQuery(document).ready(function($) {
      function aliginBanner() {
        widthWindown = $(window).width();
        widthContainer = $('.nav-container .container').width();
        widthBlogTag = $('.banner-run .block-tag').width();
        left = (widthWindown - widthContainer) / 2 - widthBlogTag;
        $('.banner-run .block-tag.tag-post').css('margin-left',left+'px');
        $('.banner-run .block-tag.tag-most').css('margin-right',left+'px');
      }
      aliginBanner()
      $(window).resize(function(event) {
        aliginBanner()
      });
      $(window).scroll(function(){
            tTop = $('.nav-container').offset().top;
            t = parseInt($(window).scrollTop());
            position = t - tTop + 20;
            if(t > tTop) {
                $('.block-tag').stop().animate({marginTop:position},1000,'easeOutBack');
            }
            else {
                $('.block-tag').stop().animate({marginTop:20},1000,'easeOutBack');
            }
        })
    });
--------------------------------------------------------------------------
configurabel js

-----------------------------------------------------------
sticky fixed
var domFlagSticky = '.header-page';
    offsetTopHeader = 0;
    if($(domFlagSticky)) {
        offsetTopHeader = $(domFlagSticky).offset().top;
        offsetTopHeader += $(domFlagSticky).height();
    }
    if(offsetTopHeader > 0) {
        offsetTopHeader += 20;
        var marginTop = $(domFlagSticky).next().css('margin-top');
        $(window).scroll(function(event) {
            scrollTop = $(window).scrollTop();
            if(scrollTop > offsetTopHeader) {
                $(domFlagSticky).addClass('sticky');
                $(domFlagSticky).next().css('margin-top',offsetTopHeader+'px');
            }
            else {
                $(domFlagSticky).removeClass('sticky');
                $(domFlagSticky).next().css('margin-top',marginTop);
            }
        });
    }
-----------------------------------------------
collase readmore
$.fn.collapseReadMore = function(object) {
    var thisDom = $(this),
    defaultConfig = {
        textMore: 'read more',
        textLess: 'less',
        line: 2,
        classLess: '',
        classMore: '',
        domReference: false,
        addMarginChild: true,
        showInline: false
    },
    config = $.extend(defaultConfig,object),
    heightDom = thisDom.height(),
    showMore = $('<div/>')
        .addClass('link')
        .addClass('readmore-wrapper')
        .addClass(config.classMore)
        .css('display','none')
        .html('<a class="link-readmore" href="#">'+config.textMore+'</a>'),
    showLess = $('<div/>')
        .addClass('link')
        .addClass('readmore-wrapper')
        .addClass(config.classMore)
        .css('display','none')
        .html('<a class="link-readless" href="#">'+config.textLess+'</a>');
    if(config.height == undefined) {
        lineHeight = parseFloat(thisDom.css('line-height'));
        config.height = lineHeight * config.line;
    }
    if(config.domReference && $(config.domReference).length) {
        heightDomReference = $(config.domReference).height();
        if(heightDomReference > config.height) {
            config.height = heightDomReference;
        }
    }
    if (heightDom > config.height){
        if(config.showInline) {
            thisDom.append(showMore);
            thisDom.append(showLess);
        }
        else {
            thisDom.after(showMore);
            thisDom.after(showLess);
        }

        thisDom.css({'overflow':'hidden'});
        if(config.addMarginChild && thisDom.children().length) {
            marginChild = thisDom.children().first().offset().top - thisDom.offset().top;
            config.height += marginChild;
        }
        thisDom.css({'max-height': config.height +'px'});
        showMore.show();
        $('a.link-readmore', showMore).click(function(event){
            event.preventDefault();
            thisDom.css('max-height', 'none');
            showMore.hide();
            showLess.show();
        });
        $('a.link-readless', showLess).click(function(event){
            event.preventDefault();
            thisDom.css('max-height', config.height+'px');
            showLess.hide();
            showMore.show();
        });
    }
}
$.fn.collapseReadMore=function(e){var s=$(this),h={textMore:"read more",textLess:"less",line:2,classLess:"",classMore:"",domReference:!1,addMarginChild:!0,showInline:!1},i=$.extend(h,e),a=s.height(),n=$("<div/>").addClass("link").addClass("readmore-wrapper").addClass(i.classMore).css("display","none").html('<a class="link-readmore" href="#">'+i.textMore+"</a>"),l=$("<div/>").addClass("link").addClass("readmore-wrapper").addClass(i.classMore).css("display","none").html('<a class="link-readless" href="#">'+i.textLess+"</a>");void 0==i.height&&(lineHeight=parseFloat(s.css("line-height")),i.height=lineHeight*i.line),i.domReference&&$(i.domReference).length&&(heightDomReference=$(i.domReference).height(),heightDomReference>i.height&&(i.height=heightDomReference)),a>i.height&&(i.showInline?(s.append(n),s.append(l)):(s.after(n),s.after(l)),s.css({overflow:"hidden"}),i.addMarginChild&&s.children().length&&(marginChild=s.children().first().offset().top-s.offset().top,i.height+=marginChild),s.css({"max-height":i.height+"px"}),n.show(),$("a.link-readmore",n).click(function(e){e.preventDefault(),s.css("max-height","none"),n.hide(),l.show()}),$("a.link-readless",l).click(function(e){e.preventDefault(),s.css("max-height",i.height+"px"),l.hide(),n.show()}))};

----------------------- ------------------------------------------------- --------------------------
disable submit
jQuery(document).ready(function($) {
        var subscribeSubmit = false;
        $(document).on('submit','#newsletter-validate-detail',function(event) {
            if($(this).find('.validation-failed').length) {
                return false;
            }
            else {
                if(subscribeSubmit) {
                    return false;
                }
                subscribeSubmit = true;
                return true;
            }
        });
    });
----------------------- ------------------------------------------------- --------------------------
trim
    function trimSlash(x) {
        return x.replace(/^[\/]+|[\/]+$/gm,'');
    }

----------------------- ------------------------------------------------- --------------------------
format js
return x.replace(/./g, function(c, i, a) {
        return i && c !== "." && ((a.length - i) % 3 === 0) ? ',' + c : c;
    });
----------------------- ------------------------------------------------- --------------------------

----------------------- ------------------------------------------------- --------------------------

----------------------- ------------------------------------------------- --------------------------

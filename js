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
click without dom
var clicky;
$(document).mousedown(function(e) {
    clicky = e.target;
});
$(document).mouseup(function() {
    clicky = null;
});
$.fn.isClickWithoutDom = function(option) {
    if (option == undefined || 
        option.container == undefined || 
        option.except == undefined
    ) {
        return false;
    }
    if (option.type == undefined) {
        option.type = 0;
    }
    if (option.except.is(clicky)
        || option.except.has(clicky).length !== 0
    ){
        return false;
    } else if (! option.container.is(clicky)
        && option.container.has(clicky).length === 0
    ) {
        return true;
    }
};
----------------------- ------------------------------------------------- --------------------------
var loadJS = function(url, implementationCode, location){
    //url is URL of external file, implementationCode is the code
    //to be called from the file, location is the location to
    //insert the <script> element

    var scriptTag = document.createElement('script');
    scriptTag.src = url;

    scriptTag.onload = implementationCode;
    scriptTag.onreadystatechange = implementationCode;

    location.appendChild(scriptTag);
};
var yourCodeToBeCalled = function(){
//your code goes here
}
loadJS('https://code.jquery.com/jquery-3.4.1.min.js', false, document.body);



----------------------- ------------------------------------------------- --------------------------
git get branch
var gitBranch = '';
$('.git-revision-dropdown .dropdown-content ul li:not(.dropdown-header)').each(function(i,v) {
    gitBranch += $(v).find('a').html() + ' ';
});
console.log(gitBranch);

// branch merge create
var branchMerge = '';

gitBranch = gitBranch.split(/\s+/);
branchMerge = branchMerge.split(/\s+/);
var resultDelete = '';
gitBranch.forEach(function (item) {
    if (branchMerge.indexOf(item) === -1) { // not merge => delete
        resultDelete += item + ' ';
    }
});
console.log(resultDelete);
----------------------- ------------------------------------------------- --------------------------
url params;
var SDFunction = {
        urlParams: function () {
            var params = [];
            decodeURIComponent(window.location.search).replace(/[?&]+([^=&]+)=([^&]*)/gi, function (str, key, value) {
                params.push({key: key, value: value});
            });
            return params;
        },
        paramsArrayToUrl: function (params) {
            var url = '';
            $.each (params, function (index, obj) {
                url += obj.key + '=' + obj.value + '&';
            });
            return encodeURI(url.slice(0, -1));
        },
        urlReplace: function (newParams, url) {
            if (typeof url === 'undefined' || url === null) {
                url = location.origin + location.pathname;
            }
            var params = [];
            var oldParams = this.urlParams();
            $.each (oldParams, function (index, pObj) {
                var isAssign = false;
                $.each (newParams, function (kNew, vNew) {
                    if (pObj.key === kNew) {
                        params.push({key: kNew, value: vNew});
                        isAssign = true;
                        delete newParams[kNew];
                        return false;
                    }
                });
                if (!isAssign) {
                    params.push(pObj);
                }
            });
            $.each (newParams, function (kNew, vNew) {
                params.push({key: kNew, value: vNew});
            });
            if ($.isEmptyObject(params)) {
                return url;
            }
            return url + '?' + this.paramsArrayToUrl(params);
        }
    };
----------------------- ------------------------------------------------- --------------------------
download img

var loadJS = function(url, implementationCode, location){
    //url is URL of external file, implementationCode is the code
    //to be called from the file, location is the location to
    //insert the <script> element

    var scriptTag = document.createElement('script');
    scriptTag.src = url;

    scriptTag.onload = implementationCode;
    scriptTag.onreadystatechange = implementationCode;

    location.appendChild(scriptTag);
};
loadJS('https://code.jquery.com/jquery-3.4.1.min.js', false, document.body);

var downloadImg = function(url) {
    var image = new Image();
    image.crossOrigin = "anonymous";
    image.src = url;
    // get file name - you might need to modify this if your image url doesn't contain a file extension otherwise you can set the file name manually
    var fileName = image.src.split(/(\\|\/)/g).pop();
    image.onload = function () {
        var canvas = document.createElement('canvas');
        canvas.width = this.naturalWidth; // or 'width' if you want a special/scaled size
        canvas.height = this.naturalHeight; // or 'height' if you want a special/scaled size
        canvas.getContext('2d').drawImage(this, 0, 0);
        var blob;
        // ... get as Data URI
        if (image.src.indexOf(".jpg") > -1) {
            blob = canvas.toDataURL("image/jpeg");
        } else if (image.src.indexOf(".png") > -1) {
            blob = canvas.toDataURL("image/png");
        } else if (image.src.indexOf(".gif") > -1) {
            blob = canvas.toDataURL("image/gif");
        } else {
            blob = canvas.toDataURL("image/png");
        }
        if (jQuery("#image-download").length) {
            jQuery("#image-download").attr('download', fileName).attr('href', blob);
            jQuery("#image-download img").attr('src', blob);
        } else {
            jQuery("body")
                .append("<a id='image-download' download='" + fileName + "' href='" + blob + "'><img src='" + blob + "'/></a>");
        }
        jQuery('#image-download img').click();
    };
}

var sleep = function(milliseconds) {
  const date = Date.now();
  let currentDate = null;
  do {
    currentDate = Date.now();
  } while (currentDate - date < milliseconds);
}

function downloadAll() {
    var indexSrcs = 0;
    var srcs = getAllLinks();
    var intervalDownload =  setInterval(function () {
        console.log(indexSrcs, srcs[indexSrcs])
        downloadImg(srcs[indexSrcs]);
        indexSrcs++;
        if (indexSrcs >= srcs.length) {
            clearInterval(intervalDownload);
        }
    }, 500);
}

var getAllLinks = function () {
    var srcs = [];
    jQuery('.app-page-section.grid-section .icon').each(function (i, v) {
        var src = $(v).find('.app-icon').find('img').attr('src');
        srcs.push(src);
    });
    return srcs;
}

downloadAll();
----------------------- ------------------------------------------------- --------------------------
slug

var chars = {
    '0': ['°', '₀', '۰', '０'],
    '1': ['¹', '₁', '۱', '１'],
    '2': ['²', '₂', '۲', '２'],
    '3': ['³', '₃', '۳', '３'],
    '4': ['⁴', '₄', '۴', '٤', '４'],
    '5': ['⁵', '₅', '۵', '٥', '５'],
    '6': ['⁶', '₆', '۶', '٦', '６'],
    '7': ['⁷', '₇', '۷', '７'],
    '8': ['⁸', '₈', '۸', '８'],
    '9': ['⁹', '₉', '۹', '９'],
    'a': ['à', 'á', 'ả', 'ã', 'ạ', 'ă', 'ắ', 'ằ', 'ẳ', 'ẵ', 'ặ', 'â', 'ấ', 'ầ', 'ẩ', 'ẫ', 'ậ', 'ā', 'ą', 'å', 'α', 'ά', 'ἀ', 'ἁ', 'ἂ', 'ἃ', 'ἄ', 'ἅ', 'ἆ', 'ἇ', 'ᾀ', 'ᾁ', 'ᾂ', 'ᾃ', 'ᾄ', 'ᾅ', 'ᾆ', 'ᾇ', 'ὰ', 'ά', 'ᾰ', 'ᾱ', 'ᾲ', 'ᾳ', 'ᾴ', 'ᾶ', 'ᾷ', 'а', 'أ', 'အ', 'ာ', 'ါ', 'ǻ', 'ǎ', 'ª', 'ა', 'अ', 'ا', 'ａ', 'ä'],
    'b': ['б', 'β', 'ب', 'ဗ', 'ბ', 'ｂ'],
    'c': ['ç', 'ć', 'č', 'ĉ', 'ċ', 'ｃ'],
    'd': ['ď', 'ð', 'đ', 'ƌ', 'ȡ', 'ɖ', 'ɗ', 'ᵭ', 'ᶁ', 'ᶑ', 'д', 'δ', 'د', 'ض', 'ဍ', 'ဒ', 'დ', 'ｄ'],
    'e': ['é', 'è', 'ẻ', 'ẽ', 'ẹ', 'ê', 'ế', 'ề', 'ể', 'ễ', 'ệ', 'ë', 'ē', 'ę', 'ě', 'ĕ', 'ė', 'ε', 'έ', 'ἐ', 'ἑ', 'ἒ', 'ἓ', 'ἔ', 'ἕ', 'ὲ', 'έ', 'е', 'ё', 'э', 'є', 'ə', 'ဧ', 'ေ', 'ဲ', 'ე', 'ए', 'إ', 'ئ', 'ｅ'],
    'f': ['ф', 'φ', 'ف', 'ƒ', 'ფ', 'ｆ'],
    'g': ['ĝ', 'ğ', 'ġ', 'ģ', 'г', 'ґ', 'γ', 'ဂ', 'გ', 'گ', 'ｇ'],
    'h': ['ĥ', 'ħ', 'η', 'ή', 'ح', 'ه', 'ဟ', 'ှ', 'ჰ', 'ｈ'],
    'i': ['í', 'ì', 'ỉ', 'ĩ', 'ị', 'î', 'ï', 'ī', 'ĭ', 'į', 'ı', 'ι', 'ί', 'ϊ', 'ΐ', 'ἰ', 'ἱ', 'ἲ', 'ἳ', 'ἴ', 'ἵ', 'ἶ', 'ἷ', 'ὶ', 'ί', 'ῐ', 'ῑ', 'ῒ', 'ΐ', 'ῖ', 'ῗ', 'і', 'ї', 'и', 'ဣ', 'ိ', 'ီ', 'ည်', 'ǐ', 'ი', 'इ', 'ی', 'ｉ'],
    'j': ['ĵ', 'ј', 'Ј', 'ჯ', 'ج', 'ｊ'],
    'k': ['ķ', 'ĸ', 'к', 'κ', 'Ķ', 'ق', 'ك', 'က', 'კ', 'ქ', 'ک', 'ｋ'],
    'l': ['ł', 'ľ', 'ĺ', 'ļ', 'ŀ', 'л', 'λ', 'ل', 'လ', 'ლ', 'ｌ'],
    'm': ['м', 'μ', 'م', 'မ', 'მ', 'ｍ'],
    'n': ['ñ', 'ń', 'ň', 'ņ', 'ŉ', 'ŋ', 'ν', 'н', 'ن', 'န', 'ნ', 'ｎ'],
    'o': ['ó', 'ò', 'ỏ', 'õ', 'ọ', 'ô', 'ố', 'ồ', 'ổ', 'ỗ', 'ộ', 'ơ', 'ớ', 'ờ', 'ở', 'ỡ', 'ợ', 'ø', 'ō', 'ő', 'ŏ', 'ο', 'ὀ', 'ὁ', 'ὂ', 'ὃ', 'ὄ', 'ὅ', 'ὸ', 'ό', 'о', 'و', 'θ', 'ို', 'ǒ', 'ǿ', 'º', 'ო', 'ओ', 'ｏ', 'ö'],
    'p': ['п', 'π', 'ပ', 'პ', 'پ', 'ｐ'],
    'q': ['ყ', 'ｑ'],
    'r': ['ŕ', 'ř', 'ŗ', 'р', 'ρ', 'ر', 'რ', 'ｒ'],
    's': ['ś', 'š', 'ş', 'с', 'σ', 'ș', 'ς', 'س', 'ص', 'စ', 'ſ', 'ს', 'ｓ'],
    't': ['ť', 'ţ', 'т', 'τ', 'ț', 'ت', 'ط', 'ဋ', 'တ', 'ŧ', 'თ', 'ტ', 'ｔ'],
    'u': ['ú', 'ù', 'ủ', 'ũ', 'ụ', 'ư', 'ứ', 'ừ', 'ử', 'ữ', 'ự', 'û', 'ū', 'ů', 'ű', 'ŭ', 'ų', 'µ', 'у', 'ဉ', 'ု', 'ူ', 'ǔ', 'ǖ', 'ǘ', 'ǚ', 'ǜ', 'უ', 'उ', 'ｕ', 'ў', 'ü'],
    'v': ['в', 'ვ', 'ϐ', 'ｖ'],
    'w': ['ŵ', 'ω', 'ώ', 'ဝ', 'ွ', 'ｗ'],
    'x': ['χ', 'ξ', 'ｘ'],
    'y': ['ý', 'ỳ', 'ỷ', 'ỹ', 'ỵ', 'ÿ', 'ŷ', 'й', 'ы', 'υ', 'ϋ', 'ύ', 'ΰ', 'ي', 'ယ', 'ｙ'],
    'z': ['ź', 'ž', 'ż', 'з', 'ζ', 'ز', 'ဇ', 'ზ', 'ｚ'],
    'aa': ['ع', 'आ', 'آ'],
    'ae': ['æ', 'ǽ'],
    'ai': ['ऐ'],
    'ch': ['ч', 'ჩ', 'ჭ', 'چ'],
    'dj': ['ђ', 'đ'],
    'dz': ['џ', 'ძ'],
    'ei': ['ऍ'],
    'gh': ['غ', 'ღ'],
    'ii': ['ई'],
    'ij': ['ĳ'],
    'kh': ['х', 'خ', 'ხ'],
    'lj': ['љ'],
    'nj': ['њ'],
    'oe': ['ö', 'œ', 'ؤ'],
    'oi': ['ऑ'],
    'oii': ['ऒ'],
    'ps': ['ψ'],
    'sh': ['ш', 'შ', 'ش'],
    'shch': ['щ'],
    'ss': ['ß'],
    'sx': ['ŝ'],
    'th': ['þ', 'ϑ', 'ث', 'ذ', 'ظ'],
    'ts': ['ц', 'ც', 'წ'],
    'ue': ['ü'],
    'uu': ['ऊ'],
    'ya': ['я'],
    'yu': ['ю'],
    'zh': ['ж', 'ჟ', 'ژ'],
    '(c)': ['©'],
    'A': ['Á', 'À', 'Ả', 'Ã', 'Ạ', 'Ă', 'Ắ', 'Ằ', 'Ẳ', 'Ẵ', 'Ặ', 'Â', 'Ấ', 'Ầ', 'Ẩ', 'Ẫ', 'Ậ', 'Å', 'Ā', 'Ą', 'Α', 'Ά', 'Ἀ', 'Ἁ', 'Ἂ', 'Ἃ', 'Ἄ', 'Ἅ', 'Ἆ', 'Ἇ', 'ᾈ', 'ᾉ', 'ᾊ', 'ᾋ', 'ᾌ', 'ᾍ', 'ᾎ', 'ᾏ', 'Ᾰ', 'Ᾱ', 'Ὰ', 'Ά', 'ᾼ', 'А', 'Ǻ', 'Ǎ', 'Ａ', 'Ä'],
    'B': ['Б', 'Β', 'ब', 'Ｂ'],
    'C': ['Ç', 'Ć', 'Č', 'Ĉ', 'Ċ', 'Ｃ'],
    'D': ['Ď', 'Ð', 'Đ', 'Ɖ', 'Ɗ', 'Ƌ', 'ᴅ', 'ᴆ', 'Д', 'Δ', 'Ｄ'],
    'E': ['É', 'È', 'Ẻ', 'Ẽ', 'Ẹ', 'Ê', 'Ế', 'Ề', 'Ể', 'Ễ', 'Ệ', 'Ë', 'Ē', 'Ę', 'Ě', 'Ĕ', 'Ė', 'Ε', 'Έ', 'Ἐ', 'Ἑ', 'Ἒ', 'Ἓ', 'Ἔ', 'Ἕ', 'Έ', 'Ὲ', 'Е', 'Ё', 'Э', 'Є', 'Ə', 'Ｅ'],
    'F': ['Ф', 'Φ', 'Ｆ'],
    'G': ['Ğ', 'Ġ', 'Ģ', 'Г', 'Ґ', 'Γ', 'Ｇ'],
    'H': ['Η', 'Ή', 'Ħ', 'Ｈ'],
    'I': ['Í', 'Ì', 'Ỉ', 'Ĩ', 'Ị', 'Î', 'Ï', 'Ī', 'Ĭ', 'Į', 'İ', 'Ι', 'Ί', 'Ϊ', 'Ἰ', 'Ἱ', 'Ἳ', 'Ἴ', 'Ἵ', 'Ἶ', 'Ἷ', 'Ῐ', 'Ῑ', 'Ὶ', 'Ί', 'И', 'І', 'Ї', 'Ǐ', 'ϒ', 'Ｉ'],
    'J': ['Ｊ'],
    'K': ['К', 'Κ', 'Ｋ'],
    'L': ['Ĺ', 'Ł', 'Л', 'Λ', 'Ļ', 'Ľ', 'Ŀ', 'ल', 'Ｌ'],
    'M': ['М', 'Μ', 'Ｍ'],
    'N': ['Ń', 'Ñ', 'Ň', 'Ņ', 'Ŋ', 'Н', 'Ν', 'Ｎ'],
    'O': ['Ó', 'Ò', 'Ỏ', 'Õ', 'Ọ', 'Ô', 'Ố', 'Ồ', 'Ổ', 'Ỗ', 'Ộ', 'Ơ', 'Ớ', 'Ờ', 'Ở', 'Ỡ', 'Ợ', 'Ø', 'Ō', 'Ő', 'Ŏ', 'Ο', 'Ό', 'Ὀ', 'Ὁ', 'Ὂ', 'Ὃ', 'Ὄ', 'Ὅ', 'Ὸ', 'Ό', 'О', 'Θ', 'Ө', 'Ǒ', 'Ǿ', 'Ｏ', 'Ö'],
    'P': ['П', 'Π', 'Ｐ'],
    'Q': ['Ｑ'],
    'R': ['Ř', 'Ŕ', 'Р', 'Ρ', 'Ŗ', 'Ｒ'],
    'S': ['Ş', 'Ŝ', 'Ș', 'Š', 'Ś', 'С', 'Σ', 'Ｓ'],
    'T': ['Ť', 'Ţ', 'Ŧ', 'Ț', 'Т', 'Τ', 'Ｔ'],
    'U': ['Ú', 'Ù', 'Ủ', 'Ũ', 'Ụ', 'Ư', 'Ứ', 'Ừ', 'Ử', 'Ữ', 'Ự', 'Û', 'Ū', 'Ů', 'Ű', 'Ŭ', 'Ų', 'У', 'Ǔ', 'Ǖ', 'Ǘ', 'Ǚ', 'Ǜ', 'Ｕ', 'Ў', 'Ü'],
    'V': ['В', 'Ｖ'],
    'W': ['Ω', 'Ώ', 'Ŵ', 'Ｗ'],
    'X': ['Χ', 'Ξ', 'Ｘ'],
    'Y': ['Ý', 'Ỳ', 'Ỷ', 'Ỹ', 'Ỵ', 'Ÿ', 'Ῠ', 'Ῡ', 'Ὺ', 'Ύ', 'Ы', 'Й', 'Υ', 'Ϋ', 'Ŷ', 'Ｙ'],
    'Z': ['Ź', 'Ž', 'Ż', 'З', 'Ζ', 'Ｚ'],
    'AE': ['Æ', 'Ǽ'],
    'Ch': ['Ч'],
    'Dj': ['Ђ'],
    'Dz': ['Џ'],
    'Gx': ['Ĝ'],
    'Hx': ['Ĥ'],
    'Ij': ['Ĳ'],
    'Jx': ['Ĵ'],
    'Kh': ['Х'],
    'Lj': ['Љ'],
    'Nj': ['Њ'],
    'Oe': ['Œ'],
    'Ps': ['Ψ'],
    'Sh': ['Ш'],
    'Shch': ['Щ'],
    'Ss': ['ẞ'],
    'Th': ['Þ'],
    'Ts': ['Ц'],
    'Ya': ['Я'],
    'Yu': ['Ю'],
    'Zh': ['Ж'],
    ' ': ["\xC2\xA0", "\xE2\x80\x80", "\xE2\x80\x81", "\xE2\x80\x82", "\xE2\x80\x83", "\xE2\x80\x84", "\xE2\x80\x85", "\xE2\x80\x86", "\xE2\x80\x87", "\xE2\x80\x88", "\xE2\x80\x89", "\xE2\x80\x8A", "\xE2\x80\xAF", "\xE2\x81\x9F", "\xE3\x80\x80", "\xEF\xBE\xA0"],
}

var slug = function(str) {
  str = str.replace(/^\s+|\s+$/g, '');
  var slugSplit = '_';
  for (keySlug in chars) {
    for (i in chars[keySlug]) {
        str = str.replace(new RegExp(chars[keySlug][i], 'g'), keySlug);
    }
  }

  str = str.toLowerCase();
  str = str.replace(/[^a-z0-9 -]/g, '')
    .replace(/\s+/g, slugSplit)
    .replace(/-+/g, slugSplit);

  return str;
};

for (i in c) {
    i.fields.image = 
}
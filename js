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
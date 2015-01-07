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
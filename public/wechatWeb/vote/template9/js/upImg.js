
 /*上传图片*/
	function fnInfo(ppp)
	{
		$('.over-hint').remove();
		$('.wrap').append('<div class="over-hint"><p>' + ppp + '</p> </div>');
		$('.over-hint').css({
			"transform": "scale(1)",
			"opacity": 1
		});
		setTimeout(function(){
			$('.over-hint').css({
				"transform": "scale(0)",
				"opacity": 0
			});
		},1000);
	}
    function fnNews(num) {
    
    $('.add-pic input').change(function () {

			var fileImage = document.getElementById('addimage').files;

			for (var i = 0; i < fileImage.length; i++) {

				if(this.files[i].type.split("/")[0]=="image") {

					var src = window.URL.createObjectURL(this.files[i]);

					var NewNode = document.createElement('div');
					NewNode.className = 'mypicture';

					var NewImage = new Image();
					NewImage.src = src;
					NewNode.appendChild(NewImage);

					var NewI = document.createElement('i');
					NewI.className = 'del-per';
					NewNode.appendChild(NewI);

					var peoplist = document.getElementsByClassName('up-img-area')[0];
					var addpic = document.getElementsByClassName('add-pic')[0];
					peoplist.insertBefore(NewNode, addpic);

					var mypictureLength = document.getElementsByClassName('mypicture');

					if( mypictureLength.length >= num) {
						addpic.style.display = 'none';
					}
				}
				else {
					fnInfo("请上传图片");
				}

				//判断只上传num张图片
				var imgboxlength =  $('.mypicture').length;
				console.log(imgboxlength);
				for (var k = 0; k < imgboxlength; k++) {
					if(k >= num) {
						$('.mypicture').eq(k).remove();
						fnInfo('"最多上传' + num + '张图片"');
					}
				} 
			}

			var input = document.getElementById('addimage');
			input.value = '';
 
			/*删除照片*/
			$(".up-img-area .mypicture").each(function(){
				$(this).find(".del-per").click(function(){
					$(this).parent(".up-img-area .mypicture").remove();
					if( $('.mypicture').length < num) {
						$('.add-pic').show();
				 		$('.add-pic').css("display", "inline-block");
					}
				})
			});
		});
    }
    fnNews(4);
function init() {//On load function
	setSize();
	setHandlers();
	//errors();
}

function setSize() {//Set size function
	$('#navContainer').width($('#navContainer').width() - 20);
}

function setHandlers(){
	
	$(window).scroll(function(){
		if($('#navContainer').offset().top <= $(window).scrollTop()){
			var navLeft = $('#navContainer').offset().left;
			$('#navContainer').css({
				'position' : 'fixed',
				'top' : 0,
				'left' : navLeft
				/*'max-width' : $(window).width(),
				'width' : $(window).width()*/
			});
		}
		if(112 >= $(window).scrollTop()){
			$('#navContainer').css({
				'position' : 'inherit'
				/*'max-width' : '1000px'*/
			});
		}
	});
	
	
	
	
	var imageNotice = false;
	var hintNotice = false;
	$('.content').click(function(){
		window.location = $(this).parent().attr('data-link');
	});
	
	$('#backgroundTextNotice').mouseenter(function(){
			if(imageNotice === false){
				$('#imageCredit').css({
					'width' : $(window).width(),
					'height' : $(window).height()
				});
			$('#imageCredit').transition({display: 'block'}).transition({y: -1 * $(window).height(), opacity: 1, duration: 500});
			setTimeout(function () {
				if(hintNotice === false){
					$('#imgHint').transition({opacity: 1, color: '#3498db', duration: 1000});
					hintNotice = true;	
				}
		    }, 10000);
		    
		    $('#navContainer').css({
				'opacity' : 0
			});
			
			
			imageNotice = true;
		}
		
	});
	
	$('#cancleImageCred').click(function(){
			$('#imageCredit').css({
				'width' : 0,
				'height' : 0
			});
			$('#imageCredit').transition({y: 0, opacity: 0,  duration: 500}).transition({display: 'none'});
			
			$('#navContainer').css({
				'opacity' : 1
			});
		imageNotice = true;
	});
	
	$('.voteBox').click(function(){
		var votesArray = $('#votes').val().split(",");
		var voteItem = $(this).attr('data-item');
		
		if($(this).attr('class').indexOf("voted") != -1){//UnVoting
			$(this).removeClass('voted');
			if(votesArray.indexOf(voteItem) == -1){
				votesArray.push(voteItem);	
			}
		} else if($(this).attr('class').indexOf("selected") != -1){//UnSelecting
			$(this).removeClass('selected');
			votesArray.splice(votesArray.indexOf(voteItem), 1);
		} else if($(this).attr('class').indexOf("voted") == -1 && $(this).attr('class').indexOf("selected") == -1){//Voting
			$(this).addClass('selected');
			if(votesArray.indexOf(voteItem) == -1){
				votesArray.push(voteItem);
			}
		}
		
		$('#votes').val(votesArray.join(","));
	});
	
	$('#login').click(function(){
		$('#loginForm').toggle('slide');
	});
	
	$('#register').click(function(){
		$('#registerForm').toggle('slide');
	});
}

function error(error){
	$('#errors').removeClass('hidden');
	$('#errors').html(error);
}

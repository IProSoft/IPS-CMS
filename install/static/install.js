$(document).ready(function() {
	
	
	var allowStep = true;
	//$.fn.wizard.logging = true;
	wizard = $('#install-wizard').wizard({
		keyboard : false,
		showClose : false,
		contentHeight : 600,
		contentWidth : ( $(window).width() < 700 ? 500 : $(window).width() - 200 ),
		backdrop: 'static',
		formClass: 'form-vertical',
		buttons: lang_buttons
	});
	
		
	wizard.setSubtitle( $('.wizard-card').first().find('h3').text() );
		
	$(".wizard-title").html('<img src="static/images/logo-ipscms.png" class="install-logo" />');
	$(".wizard-header").append('<div class="install-ver">v2.0</div>');
	
	$(".chzn-select").chosen();
	
	$('#server-stats').find('.btn-group').each(function(i) {
		var group = $(this);
		$.post("install-check.php", { check_server : 'true', func : group.attr('data-check') }, function(result){
			setTimeout(function(){ 
				var buttons = group.find('button').addClass('ready');
				
				if(result != 'true'){
					buttons.last().addClass('btn-danger').find('img').attr('src', 'static/images/error.png');
					group.find('button.info').html(result)
				}
				else
				{
					buttons.last().addClass('btn-success').find('img').attr('src', 'static/images/success.png');
				}
			}, 300 * i );
		});
	});
	
	$('#input-id').fileinput({
		browseLabel: "Wybierz &hellip;",
		removeLabel: "Usuń",
		uploadUrl: "static/upload/",
		showUpload: false,
		allowedFileExtensions: ['png'],
		msgInvalidFileExtension: 'Plik "{name}" ma nieprawidłowe rozszerzenie. Dozwolone są tylko pliki "{extensions}"',
		dropZoneEnabled: false
	}).on('fileimageloaded', function(event, previewId) {
		$('.kv-file-upload').trigger('click');
	});
	
/*
	$this.parents('.form-group').removeClass('has-error has-success').addClass('has-error');
	$this.parents('.form-group').removeClass('has-error').addClass('has-success');
*/
	
	$('.alert').on('click', '.close', function () {
		$(this).parent().addClass('hide');
	})
	
	$('.rand-password').on('click',  function () {
		var rand = __rand( 10, 'string' );
		$('.rand-password-input').val( rand ).attr( 'type', 'text' );
	})
	$('.rand-password-input').on('keyup', function(){
		$('.rand-password-input').attr( 'type', 'password' );
	})
	$('input').on('input', function() {
		if ($(this).val().length != 0) {
			$('#ip').val('').attr('disabled', 'disabled');
			$(this).parents('.form-group').removeClass('has-error has-success');
		}
	});
	
	wizard.on('closed', function() {
		wizard.reset();
	});

	wizard.on("reset", function() {
		wizard.modal.find(':input').val('').removeAttr('disabled');
		wizard.modal.find('.form-group').removeClass('has-error').removeClass('has-succes');
	});

	function returnFormStatus( result, partial )
	{
		if( typeof result.error != 'undefined' )
		{
			setTimeout(function() {
				wizard.trigger("error");
				wizard.showSubmitCard("error");
			}, 2000);
			
			return $('.install-error span').html( result.error );
		}
		if( partial )
		{
			return true;
		}
		if( typeof result.info != 'undefined' )
		{
			$('.install-problems div').html( result.info ).removeClass('hide');
			result.success = true;
		}
		
		if( typeof result.success != 'undefined' )
		{
			$('.admin-info .email span').html( $('input[name="admin[email]"]').val() );
			$('.admin-info .login span').html( $('input[name="admin[username]"]').val() );
			$('.admin-info .password span').html( $('input[name="admin[password]"]').val() );
			
			return setTimeout(function() {
				wizard.trigger("success");
				wizard.hideButtons();
				wizard._submitting = false;
				wizard.showSubmitCard("success");
				wizard.updateProgressBar(0);
			}, 2000);
		}
		
		return setTimeout(function() {
			wizard.hideButtons();
			wizard._submitting = false;
			wizard.trigger("failure");
			wizard.showSubmitCard("failure");
			$('.install-failure span').html( result );
		}, 2000);
	}
	wizard.on("submit", function(wizard) {

		var form = this.serialize();
		$.post("install-check.php", form, function(result){
			
			if( typeof result.info != 'undefined' )
			{
				
				$(".progres-tables").counterUp({to : result.info.tables }, function(){
					
					$(".progres-settings").counterUp({to : result.info.settings }, function(){
						
						$(".progres-translations").counterUp({to : result.info.translations }, function(){
							
							$(".wizard-loader").css('width', $(".wizard-loader").parent().outerWidth() ).show();
							$.post("install-check.php", form + '&install-data=true' , function(result){
								setTimeout(function() {
									returnFormStatus( result, false );
								}, 1250);
							}, "json");
							
						});
						
					});
					
				});
				return true;
			}
			
			returnFormStatus( result, true );

		}, "json");
	
	});
	wizard.el.find(".wizard-failure .im-done").click(function() {
		setTimeout(function() {
			$('input[name="database[host]"]').val('localhost');
			$('input[name="database[port]"]').val('3306');
			wizard.reset();	
		}, 250);
	});
	
	wizard.on("reset", function() {
		
	});
	
	wizard.on("incrementCard", function() {
		var card = wizard.getActiveCard();
		
		wizard.setSubtitle( card.el.find('h3').first().text() );
	});
	
	wizard.on("decrementCard", function() {
		var card = wizard.getActiveCard();
		
		wizard.setSubtitle( card.el.find('h3').first().text() );
	});
	
	mysqlValid = false;
	
	wizard.cards["mysql"].on("validate", function(card) {
		
		var inputs = card.el.find("input").not('.no-validate'), submit = true;

		inputs.each(function(){
			if ( $(this).val() == "" ) {
				card.wizard.errorPopover( $(this), $('.input-error-text').text());
				submit = false;
			}
			else
			{
				card.wizard.destroyPopover( $(this) );
			}
		});
		
		if( submit )
		{
			if( mysqlValid )
			{
				return true;
			}
			validateDB( inputs );
		}
		
		return false;
		
	});
	
	$(".wizard-group-list").click(function() {
		alert("Disabled for demo.");
	});

	wizard.show();
	
});
function __rand( l, charSet ){
	charSet = charSet && charSet == 'string' ? 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ' : '0123456789';
	var randomString = '';
	for (var i = 0; i < l; i++) {
		var randomPoz = Math.floor(Math.random() * charSet.length);
		randomString += charSet.substring(randomPoz,randomPoz+1);
	}
	return randomString;
}
function validateDB( inputs )
{
	$(".mysql-loader").removeClass('hide');
	$(".mysql-error").addClass('hide');
	
	timeout = setTimeout(function() {
		if( !$(".mysql-loader").hasClass('hide') )
		{
			$(".mysql-loader").html( $('.input-loading-text').text() );
		}
	}, 25000);
	
	$.post("install-check.php", inputs.serialize(), function(result){
		clearTimeout( timeout );
		if( result == 'false' )
		{
			$(".mysql-loader").addClass('hide');
			
			if( !$(".mysql-error").hasClass('hide') )
			{
				$(".mysql-error").addClass('hide');
			}
			
			$(".mysql-error").fadeIn( 'fast', function(){
				$(".mysql-error").removeClass('hide').removeAttr('style');
			});
			
			return false;
		}
		
		mysqlValid = true;
		wizard.setCard( wizard.cards["mysql"].index + 1 );
	});

}

function validateServer(object) {
	var retValue = {};

	if ( object.el.find('button.ready').length != object.el.find('button.btn').length  ) {
		
		$(".server-check").removeClass('hide');
		return false;
	}
	
	if ( object.el.find('button.btn-success').length != object.el.find('button.btn').length / 3  ) {
		
		$(".server-error").removeClass('hide');
		return false;
	}
	
	return true;
};

function validateInput(el) {
	var name = el.val();
	var retValue = {};

	if (name == "") {
		retValue.status = false;
		retValue.msg = $('.input-error-text').text();
	} else {
		retValue.status = true;
	}
	return retValue;
};

function validateHelper( el, regex )
{
	var value = el.val();
	var retValue = {};
	
	
	if (value == "" || ( typeof regex != 'undefined' && !regex.test( value ) ) ) {
		retValue.status = false;
		retValue.msg = el.parent().find( '.input-validation' ).text();
	} else {
		retValue.status = true;
	}
	return retValue;
}
function validateEmail( el )
{
	var regex = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
	
	return validateHelper( el, regex );
}
function validateUsername( el )
{
	var regex = /^([a-zA-Z]{2,20})$/;
	
	return validateHelper( el, regex );
}
function validatePassword( el )
{
	var regex = /^([a-zA-Z]{2,20})$/;
	
	return validateHelper( el, regex );
}
function validatePasswordRepeat( el )
{
	var value = el.val();
	
	var retValue = {};

	if (value == "" || $( '.password-input' ).val() !== value ) {
		retValue.status = false;
		retValue.msg = el.parent().find( '.input-validation' ).text();
	} else {
		retValue.status = true;
	}
	return retValue;
}
function validateTemplate( el )
{
	var value = el.val();
	var retValue = {};

	if (value == "") {
		retValue.status = false;
		retValue.msg = el.parent().find( '.input-validation' ).text();
	} else {
		retValue.status = true;
	}
	return retValue;
}
function deleteTable( table )
{
	$.post("install-check.php", { delete_table: table }, function(result){
		if( result == 'true' )
		{
			$('#div_wynik_all').html('Usunięto tabelę ' + table );
		}
		else
		{
			$('#div_wynik_all').html('Wystąpił błąd.');
		}
	});
}
/*!
* jquery.counterup/upload.js 1.0
*
* Copyright 2013, Benjamin Intal http://gambit.ph @bfintal
* Released under the GPL v2 License
*
* Date: Nov 26, 2013
*/
(function( $ ){
  "use strict";

  $.fn.counterUp = function( options, callback ) {

    // Defaults
    var settings = $.extend({
        'time': 500,
        'delay': 10
    }, options);

    return this.each(function(){

        // Store the object
        var $this = $(this);
		var $counter = $(this).find('.btn-counter');
        var $settings = settings;

		var nums = [];
		var divisions = $settings.time / $settings.delay;
		var num = $settings.to;

		var isInt = /^[0-9]+$/.test(num);
		var isFloat = /^[0-9]+\.[0-9]+$/.test(num);
		var decimalPlaces = isFloat ? (num.split('.')[1] || []).length : 0;

		// Generate list of incremental numbers to display
		for (var i = divisions; i >= 1; i--) {

			// Preserve as int if input was int
			var newNum = parseInt(num / divisions * i);

			// Preserve float if input was float
			if (isFloat) {
				newNum = parseFloat(num / divisions * i).toFixed(decimalPlaces);
			}



			nums.unshift(newNum);
		}

		$this.data('counterup-nums', nums);
		$counter.text( '0' );

		// Updates the number until we're done
		var f = function() {
			
			$counter.text( String( $this.data('counterup-nums').shift() ) + '/' + String( num ) );
		
			if ($this.data('counterup-nums').length) {
				setTimeout($this.data('counterup-func'), $settings.delay);
			} else {
				callback();
				$counter.addClass('btn-success')
				delete $this.data('counterup-nums');
				$this.data('counterup-nums', null);
				$this.data('counterup-func', null);
			}
		};
		$this.data('counterup-func', f);

		// Start the count up
		setTimeout($this.data('counterup-func'), $settings.delay);

    });

  };

})( jQuery );

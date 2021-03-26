$(document).ready(function() {

	// Search bar expand animation
	$('#search_text_input').focus(function() {

		if(window.matchMedia("(min-width: 800px)").matches) {

			$(this).animate({width: '250px'}, 500);

		}

	});

	// Submit search when pressing magnifying glass
	$('.button_holder').on('click', function () {

		document.search_form.submit();

	});


    // Profile posting button
    $('#submit_profile_post').click(function(){

        $.ajax({    // Takes user posts from within modal and calls to ajax_submit_profile_post to post to main timeline
            type: "POST",
			url: "Includes/Handlers/ajax_submit_profile_post.php",
			data: $('form.profile_post').serialize(),
			success: function(msg) {
				$("#post_form").modal('hide');
                location.reload();
			},
			error: function() {
				alert('Failure');
            }
        });

	});

});		// End of (document).ready(function()


// When clicking anywhere on the page this closes the dropdown menus and search bar expansion
$(document).click(function(e) {

	if(e.target.class != "search_results" && e.target.id != "search_test_input") {

		$(".search_results").html("");
		$('.search_results_footer').html("");
		$('.search_results_footer').toggleClass("search_results_footer_empty");
		$('.search_results_footer').toggleClass("search_results_footer");

	}

	if(e.target.class != "dropdown_data_window") {

		$(".dropdown_data_window").html("");
		$(".dropdown_data_window").css({"padding": "0px", "height": "0px"});

	}

});


function getUsers(value, user) {
	// Takes inputed data and sends to ajax_friend_search.php and appends that data to class "results" in messages.php

	$.post("Includes/Handlers/ajax_friend_search.php", {query:value, userLoggedIn:user}, function(data) {

		$(".results").html(data);

	});

} // End of getUsers function


function getDropdownData(user, type) {
	// Function creates dropdown menu and calls the ajax to populate that menu

	if($(".dropdown_data_window").css("height") == "0px") {

		var pageName;

		if(type == 'notification') {

			pageName = "ajax_load_notifications.php";
			$("span").remove("#unread_notification");

		}	else if (type == 'message') {

			pageName = "ajax_load_message.php";
			$("span").remove("#unread_message");

		}

		var ajaxreq = $.ajax({

			url: "Includes/Handlers/" + pageName,
			type: "POST",
			data: "page=1&userLoggedIn=" + user,
			cache: false,

			success: function(response) {

				$(".dropdown_data_window").html(response);
				$(".dropdown_data_window").css({"padding" : "0px", "height" : "280px", "border" : "1px solid #DADADADA"});
				$("#dropdown_data_type").val(type);

			}

		});

	}	else	{

		$(".dropdown_data_window").html("");
		$(".dropdown_data_window").css({"padding" : "0px", "height" : "0px", "border" : "none"});

	}

}	// End of getDropdownData function


function getLiveSearchUsers(value, user) {
	// When entering a search term in the search bar this creates the ajax call for search results

	$.post("Includes/Handlers/ajax_search.php", {query:value, userLoggedIn:user}, function(data) {		// data = info resulted by ajax call

		if($(".search_results_footer_empty")[0]) {

			$(".search_results_footer_empty").toggleClass("search_results_footer");						// If value will add class
			$(".search_results_footer_empty").toggleClass("search_results_footer_empty");				// If not value, adds other class

		}

		// Displays results from ajax call with the CSS values above and then adds a element to see all search results

		$('.search_results').html(data);
		$('.search_results_footer').html("<a href='search.php?q=" + value + "'> See All Results </a>");

		if(data == "") {																					// If no data returned from ajax

			$('.search_results_footer').html("");
			$('.search_results_footer').toggleClass("search_results_footer_empty");
			$('.search_results_footer').toggleClass("search_results_footer");

		}

	}); 

}
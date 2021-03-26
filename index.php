	<?php

	include("Includes/header.php");

	if(isset($_POST['post'])) {

		// Image upload section
		$uploadOk = 1;
		$imageName = $_FILES['file_to_upload']['name'];
		$errorMessage = "";

		if ($imageName != "") {

			// Image name
			$targetDir = "Assets/Images/Posts/";
			$imageName = $targetDir . uniqid() . basename($imageName);

			// Image file type
			$imageFileType = pathinfo($imageName, PATHINFO_EXTENSION);

			// File size check
			if ($_FILES['file_to_upload']['size'] > 500000000) {

				$errorMessage = "Sorry your file is too large";
				$uploadOk = 0;

			}

			// File type check
			if (strtolower($imageFileType) != "jpeg" && strtolower($imageFileType) != "png" && strtolower($imageFileType) != "jpg") {

				$errorMessage = "Sorry only, only jpeg, jpg and png files are allowed";
				$uploadOk = 0;

			}

			// Image upload
			if ($uploadOk) {

				// If file successfully transferred to image folder
				if (move_uploaded_file($_FILES['file_to_upload']['tmp_name'], $imageName)) {

					// Image upload 

				}	else	{

					$uploadOk = 0;

				}

			}

		}	// End of IF ($imageName != "")

		if ($uploadOk) {

			$post = new Post($con, $userLoggedIn);                      			// Creating instance of Post object
			$post->submitPost($_POST['post_text'], 'none', $imageName);             // Using post object to submit post to database
			header("Location: index.php");                              			// Prevents refreshing of screen from resubmitting post

		}	else	{

			echo "	<div style='text-align: center;' class='alert alert-danger'>
						$errorMessage			
					</div>";

		}
		
	}

	?>

    <div class="user_details column">

        <!-- User profile picture -->
        <a href="<?php echo $userLoggedIn; ?>"> <img src="<?php echo $user['profile_pic'] ?>" alt="Profile Picture"> </a>
        
        
        <div class="user_details_data">
            <!-- User first and last name -->
            <a href="<?php echo $userLoggedIn; ?>">
            <?php
            echo $user['first_name'] . " " . $user['last_name'];
            ?>
            </a>

            <br>
            
            <!-- User profile data (likes and posts) -->
            <?php 
            echo "Posts: " . $user['num_posts'] . "<br>"; 
            echo "Likes: " . $user['num_likes'];
            ?>
        </div>

    </div>


	<!-- Main posts column -->
    <div class="main_column column">

        <form class="post_form" action="index.php" method="POST" enctype="multipart/form-data">

			<!-- Image upload area -->
			<input type="file" name="file_to_upload" id="file_to_upload">
            <!-- User posting text area -->
            <textarea name="post_text" id="post_text" placeholder="Got something to say?"></textarea>
            <!-- Posting textarea submit button -->
            <input type="submit" name="post" id="post_button" value="Post">
            <hr>

        </form>

		<!-- First 10 posts loaded here -->
		<div class="posts_area"></div>
		<!-- 
			Loading gif below is shown when hitting bottom of screen and next 10 posts are loaded
			 loading gif is hidden when posts load up 
		-->
		<img id="loading" src="Assets/Images/Icons/loading.gif">

    </div>

	<!-- Trending words section -->
	<div class="user_details column">
		<div class="trends">
		
			<?php

				echo "<h4 class='trending-words'> Trending Words </h4>";

				$query = mysqli_query($con, "SELECT * FROM trends ORDER BY hits DESC LIMIT 10");
				$number = mysqli_num_rows($query);

				foreach ($query as $row) {

					$word = $row['title'];
					$word_dot = strlen($word) >= 14 ? "..." : ""; 						// If word is longer than 14 characters add 3 dots

					$trimmed_word = str_split($word, 14);
					$trimmed_word = $trimmed_word[0];

					echo "<div style='padding: 1px'>";
					echo $trimmed_word . $word_dot;
					echo "<br></div><br>";

				}

			?>

		</div>
	</div>

	<script>																			// AJAX - calls handler ajax_load_posts and loads most recent 10 posts															
		var userLoggedIn = '<?php echo $userLoggedIn; ?>';								// Creates user loggin variable

		$(document).ready(function() {

			$('#loading').show();														// Displays loading.gif

			//Original ajax_load_posts.php request for loading first 10 posts 
			$.ajax({
				url: "Includes/Handlers/ajax_load_posts.php",
				type: "POST",
				data: "page=1&userLoggedIn=" + userLoggedIn,
				cache: false,

				success: function(data) {
					$('#loading').hide();												// Hides loading.gif
					$('.posts_area').html(data);										// displays first 10 posts
				}
			});

			//alert("Test 1");   														// Error Checking - AJAX

			$(window).scroll(function() {
				var height = $('.posts_area').height(); 								// Height of Div containing 10 posts
				var scroll_top = $(this).scrollTop();									// Defined top of page
				var page = $('.posts_area').find('.nextPage').val();					// Find next ten posts
				var noMorePosts = $('.posts_area').find('.noMorePosts').val();			// If there are no more posts- post this message

				// When user scrolls to bottom of page this script kicks in to load 10 more posts
				if ((document.body.scrollHeight <= document.scrollingElement.scrollTop + window.innerHeight + 1) && noMorePosts == 'false') {
					// If the user scrolls to the bottom of the page add posts until there are no more posts
					// Issue with this if statement - my window for some reason was off by a few decimal places so fixed with +1

					$('#loading').show();												// Displays loading.gif

					//alert("Test 2");													// Error Checking - IF STATEMENT

					// Next call to ajax for next 10 posts
					var ajaxReq = $.ajax({
						url: "Includes/Handlers/ajax_load_posts.php",
						type: "POST",
						data: "page=" + page + "&userLoggedIn=" + userLoggedIn,
						cache:false,

						success: function(response) {
							$('.posts_area').find('.nextPage').remove(); 				// Removes current limit - limit set in Post.php
							$('.posts_area').find('.noMorePosts').remove(); 			// Removes NoMorePosts statement - Post.php 

							$('#loading').hide();										// Hides loading.gif
							$('.posts_area').append(response);							// Adds next 10 posts to existing 10 posts
						}


					});

				} //End of IF (document.body.scrollHeight == document.body.scrollTop + window.innerHeight) && noMorePosts == 'false') 

				return false;

			}); //End (window).scroll(function())

		});	// End of (document).ready(function()

	</script>



</div>  <!-- Closed tag for wrapper div (in header) -->

</body>
</html>
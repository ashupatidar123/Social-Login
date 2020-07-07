<?php
//=======Footer.php=================
<!-- SOCIAL LOGIN -->
<script src="https://apis.google.com/js/client:platform.js" async defer></script>
<script type="text/javascript">
    
/*SOCIAL LOGIN */
function socialLogin(fbData) {

    $.ajax({
        type: "POST",
        url: baseUrl + 'front/login/socialLogin',
        data: fbData,
        beforeSend: function() {
            $('#loadingDiv').show();
        },
        success: function(response) {
            
            $('#loadingDiv').hide();
            if(response=='success'){
                window.location.reload();

            }else if(response=='failed'){
                errorMsg('Login failed.');
                window.location.reload();

            }else if(response=='userInactive'){
                errorMsg('Your account is inactive. Contact your administrator to activate it..'); return false;
            }
        },

        error: function() {
            toastr.error(commonMsg);
        }
    });
}

function getUserData() {

    FB.api('/me', {
        fields: 'name,id,email'
    }, function(response) {

        var fbData = {
            fullName: response.name,
            socialId: response.id,
            userEmail: (response.email != '' && typeof response.email != "undefined") ? response.email : '',
            registerType: "FACEBOOK",
            userType: "USER",
            profileImage: "https://graph.facebook.com/" + response.id + "/picture?type=large"
        };
        //facebookLogout();

        socialLogin(fbData);
        console.log(fbData);
    });
}

function facebookLogout() {
    FB.logout(function() {})
}

window.fbAsyncInit = function() {
    FB.init({
        appId: '283279499531880',
        xfbml: true,
        version: 'v2.2'
    });
};


(function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    
    if (d.getElementById(id)) {
        return;
    }

    js = d.createElement(s);

    js.id = id;

    js.src = "//connect.facebook.com/en_US/sdk.js";

    fjs.parentNode.insertBefore(js, fjs);

}(document, 'script', 'facebook-jssdk'));


document.getElementById('fbLogin').addEventListener('click', function() {

    FB.login(function(response) {
        if (response.authResponse) {
            getUserData();
        }
    }, {
        scope: 'email,public_profile',
        return_scopes: true
    });

}, false);

</script>

=========================html page button fbLogin click============

<a id="fbLogin" href="javascript:void(0)" class="circle facebook">


=============Controller Login.php==================

public function socialLogin(){
		$postData = $this->input->post();
		$fullName = explode(" ",$postData['fullName']);
		$userName = $fullName[0];
		$lastName = $fullName[1];
		$userEmail = $postData['userEmail'];
		$userPassword = sha1('univ@1234');
		$socialId = $postData['socialId'];	
		$registerType = $postData['registerType'];	
		$userType = $postData['userType'];
		$profileImage = $postData['profileImage'];
		$userStatus = 'ACTIVE';
		//pd($postData);

		//CHECK IF SOCIAL USER EXIST OR NOT
      	//$checkSocialUser = $this->user_model->checkSocialUser($socialId);
		$where = array('socialId'=>$socialId);
      	$data = current($this->front_model->fetchQuery("userId,userStatus","users",$where));
      	if(empty($data)){

      		$insertData = array(
				'userType' => $userType,
				'registerType' => $registerType,
				'socialId' => $socialId, 
				'userName' =>  $userName, 
				'lastName'=> $lastName,
				'userEmail'=> $userEmail,
				'userPassword' => $userPassword, 
				'profileImage' => $profileImage, 
				'userStatus' => $userStatus
			);
			$lastId = $this->front_model->insertQuery("users",$insertData);

			$sess_array = array(
				'userId' 	=> $lastId,
				'username' 	=> $userName,
				'email' 	=> $userEmail,
				'userType' 	=> $userType,
				'logged_in' => TRUE
			);

			$_SESSION['front_session'] = $sess_array;
			echo 'success'; die();

      	}else{
      		$userId = $data['userId'];
      		$userStatus = $data['userStatus'];
      		if($userStatus == 'INACTIVE'){
      			echo 'userInactive'; die();

      		}else if($userStatus == 'ACTIVE'){
      			$sess_array = array(
					'userId' 	=> $userId,
					'username' 	=> $userName,
					'email' 	=> $userEmail,
					'userType' 	=> $userType,
					'logged_in' => TRUE
				);
				$_SESSION['front_session'] = $sess_array;
				echo 'success'; die();

      		}else{
      			echo 'failed'; die();
      		}
      	}
	}
<?php

namespace OAuth2\OpenID\Storage;


interface UserClaimsInterface
{
    // valid scope values to pass into the user claims API call
    const VALID_CLAIMS = 'profile email address phone';

    // fields returned for the claims above
    const PROFILE_CLAIM_VALUES  = 'name family_name given_name middle_name nickname preferred_username profile picture website gender birthdate zoneinfo locale updated_at';
    const EMAIL_CLAIM_VALUES    = 'email email_verified';
    const ADDRESS_CLAIM_VALUES  = 'formatted street_address locality region postal_code country';
    const PHONE_CLAIM_VALUES    = 'phone_number phone_number_verified';

  
    public function getUserClaims($user_id, $scope);
}

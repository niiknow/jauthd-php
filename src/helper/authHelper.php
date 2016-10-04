<?php
namespace MyAPI\Helper;

use \Firebase\JWT\JWT;

use Ramsey\Uuid\Uuid;

/**
 * Auth Helper
 */
class AuthHelper
{
  /**
   * determine of email confirmation token is valid
   * @param  $token the token
   * @return validation result
   */
  public function verifyEmailConfirmationToken($token) 
  {
    return $this->verifyToken($token, 'emailConfirm');
  }

  /**
   * generate an email confirmation token to be sent with sub
   * @param  $sub              the sub
   * @param  $expiresIn        optional - default 2 days in seconds
   * @return the token
   */
  public function generateEmailConfirmationToken($sub, $expiresIn) 
  {
    return $this->generateToken(['sub' => $sub ], 'emailConfirm', $expiresIn);
  }

  /**
   * verify forgot password token
   * @param  $token
   * @return true if valid
   */
  public function verifyForgotPasswordToken($token) 
  {
    return $this->verifyToken($token, 'forgotPassword');
  }

  /**
   * generate a forgot password token to be sent with sub
   * @param  $sub              the sub
   * @param  $expiresIn        optional - default other token
   * @return the token
   */
  public function generateForgotPasswordToken($sub, $expiresIn) 
  {
    return $this->generateToken(['sub' => $sub ], 'forgotPassword', $expiresIn);
  }

  /**
   * verify a token
   * @param  $token     the token
   * @param  $tokenType optional - token type
   * @return a promise
   */
  public function verifyToken($tokenString, $tokenType) 
  {
    $tokenType = $tokenType || '';
    $key = getenv('JWT_SECRET') + $tokenType;

    return $this->decodeToken($tokenString, $key);
  }

  /**
   * generate login token
   * @param  $user the user object
   * @param  $expiresIn expire in second
   * @param  $access_type 'offline' or not
   */
  public generateLoginToken($user, $expiresIn, $access_type, $isPayload) {
    $tokenPayload = $isPayload ? $user : __::Pick($user, getenv('JWT_INCLUDES'));
    $access_token = $this->generateToken($tokenPayload, '', $expiresIn);
    $result = [
      'profile' => $tokenPayload,
      'access_token' => $access_token['token'],
      'expires_in' => $access_token['expires_in'],
    ];

    if ($access_type === 'offline') {
      $result['refresh_token'] = $this->generateToken([
        'sub' => $user['id'],
        'profile' => $tokenPayload
        ], 'refresh', getenv('JWT_REFRESH_AGE'))['token'];
    }

    return result;
  }

  /**
   * generate a token with the payload json
   * @param  $payload          the token data
   * @param  $tokenType        optional token type
   * @param  $expiresIn        override expiration
   * @return the token
   */
  public function generateToken($payload, $tokenType, $expiresIn) 
  {
    $tokenType = $tokenType || '';
    $key = getenv('JWT_SECRET') + $tokenType;

    $maxExpires = getenv('JWT_AUTH_AGE') or 3600;
    $pl = array_merge_(['jti' => Uuid::uuid4()], $payload)

    if ($tokenType) {
      $maxExpires = getenv('JWT_OTHER_AGE');
    }

    if ($expiresIn) {
      if (inval($expiresIn) > $maxExpires) {
        $expiresIn = $maxExpires;
      }
    }

    $opts['expiresIn'] = $expiresIn || $opts['expiresIn'];
    /*
    $pl2 = __::pluck($pl, 'password');
    foreach($pl2 as $key => $value){
      $token->set($key, $value);
    }*/

    $result = [
      'expires_in' => $opts['expiresIn'],
      'token' => $token->getToken();
    ]};

    $token = array(
        "iss" => $app->environment["MYAPP_HOSTNAME"] or 'JAuth',
        "jti" => $pl['jti'],
        "sub" => $pl['sub'] or $pl['id'],
        "exp" => $opts['expiresIn'], // or 'ttl' => 60
        "iat" => time(),
        "nbf" => time(),
        "roles" => $opt['roles']);
 
    $jwt = \JWT::encode($token, $key);
    
    $result = [
      'expires_in' => $opts['expiresIn'],
      'token' => $jwt;
    ]};

    return $result;
  }

  /**
   * allow for decoding of jwt
   */
  private function decodeToken($token, $key) 
  {
    return \JWT::decode($jwt, $key, array('HS256'));;
  }
}
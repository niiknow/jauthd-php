<?php
namespace App\Helper;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Parser;
use \stdClass;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

class AuthHelper
{
  function __construct($app) {
    $this->PasswordHasher = new PasswordHash(12, false);
  }

  /**
   * hash a password
   * @param  $password unencrypted password
   * @return the hashed result
   */
  public function hashPassword($password)
  {
    return $this->PasswordHasher->HashPassword($password),
  }

  /*
   * compare regular password to existing/hashed password
   * @param  $password       
   * @param  $hashedPassword 
   * @return true if valid
   */
  public function comparePassword($password, $hashedPassword) 
  {
    return $this->PasswordHasher->CheckPassword($password, $hashedPassword);
  }

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
    return $this->generateToken(['sub' => $sub ], 'emailConfirm', $expiresIn || APP_TOKEN_EXPIRES_OTHER);
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
    return $this->generateToken(['sub' => $sub ], 'forgotPassword', $expiresIn || APP_TOKEN_EXPIRES_OTHER);
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
    $key = APP_TOKEN_JWT_SECRET + $tokenType;

    try {
        $token = $this->decodeToken($tokenString);
    } catch (\Exception $e) {
        return false;
    }

    return $token->verify(APP_TOKEN_JWT_ISSUER, $key));
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
    $defaults = [];
    $opts = array_merge($defaults, APP_TOKEN_JWT);
    $key = APP_TOKEN_JWT_SECRET + $tokenType;

    $maxExpires = APP_TOKEN_MAXAGE_AUTH;
    $pl = array_merge_(['jti' => Uuid::uuid4()], $payload)

    if ($tokenType) {
      $maxExpires = APP_TOKEN_MAXAGE_OTHER;
    }

    if ($expiresIn) {
      if (inval($expiresIn) > $maxExpires) {
        $expiresIn = $maxExpires;
      }
    }

    $opts['expiresIn'] = $expiresIn || $opts['expiresIn'];
    $pl2 = __::pluck($pl, 'foo');
    foreach($pl2 as $key => $value){
      $token->set($key, $value);
    }

    $token = (new Builder());
    foreach($pl2 as $key => $value){
      $token->set($key, $value);
    }

    $token->setHeader('alg','RS256')
      ->setIssuer(APP_TOKEN_JWT_ISSUER)
      ->setIssuedAt(time())// Configures the time that the token was issue (iat claim)
      ->setNotBefore(time())// Configures the time that the token can be used (nbf claim)
      ->setExpiration($expiresIn);


    $signer = new Sha256();
    $token->sign($signer, $key);// creates a signature

    $result = []
      'expires_in' => $opts['expiresIn'],
      'token' => $token->getToken();
    };

    return $result;
  }

  /**
   * allow for decoding of jwg
   * @param  {[type]} token the token
   * @return {[type]}       the decoded token
   */
  public function decodeToken($token) 
  {
    return (new Parser())->parse($token);
  }
}
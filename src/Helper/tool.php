<?php 
	
	namespace Helper;

	use Namshi\JOSE\JWS;

	class Tool{

			public function generateToken(){
				return base64_encode(mcrypt_create_iv(32));
			}

			public function generateUserToken($params){

				$issuedAt 		= time();
				$notBefore 		= $issuedAt + 10;
				$expire 		= $notBefore + 60;
				$servername 	= 'eventmngmnt';


				$data = [
					'iat' => $issuedAt,     /*Issued At time when the token is generated*/
					'jti' => $params['token'], /* json token iD*/
					'iss' => $servername, /* issuer*/
					'nbf' => $notBefore, /* Not Before*/
					'exp' => $expire, /* issuer*/
					'data' => [
						'user_id' 	=> $params['user_id'],
						'email' 	=> $params['email'],
					],
					'type' => $params['type']
				];


				$jws = new JWS(['alg' => 'RS256']);
				$jws->setPayload($data);
				$jws->sign( file_get_contents( APP_DOCUMENT_URL . 'app/Module/Middleware/private.key' ),'tests' );
				$token = $jws->getTokenString();

				return $token;
			}


	}
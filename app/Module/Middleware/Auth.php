<?php 
	namespace Middleware;

	use Namshi\JOSE\JWS;

	class Auth{

		protected $eventDB;

		function __construct(){

			// $this->eventDB = $app->eventDB;
			$this->eventDB = new \medoo( ['database_type' 	=> APP_DATABASE_TYPE,
											 	'database_name' 	=> APP_DATABASE_NAME,
											 	'server' 			=> APP_DATABASE_SERVER,
											 	'username' 			=> APP_DATABASE_USERNAME,
											 	'password' 			=> APP_DATABASE_PASSWORD,
											 	'charset' 			=> APP_DATABASE_CHARSET,] );

		}


		function __invoke($req,$res,$next){
			
			if( $req->hasHeader('AUTHORIZE') ){
				$token = $req->getHeader('AUTHORIZE')[0];
			}else if(isset( $_COOKIE['token'] )){
				$token = $_COOKIE['token'];
			}else{
				$token = false;
			}

			if($token){
				$jws = JWS::load($token,false,null);
				if($jws->verify( file_get_contents( APP_DOCUMENT_URL . 'app/Module/Middleware/public.key' ),'RS256' )){

					$payload = $jws->getPayload();

					if(SELF::checkToken( $payload['data']['user_id'],$token ) ){
						$req = $req->withAttribute('info',$payload);
						$req = $req->withAttribute('token',$token);
						$req = $req->withAttribute('user_id',$payload['data']['user_id']);

						return $next($req,$res);



					}else{
						SELF::inActiveAll($payload['data']['user_id']);
					}
				}
			}

			return $res->withJson( ['error'=>['message'=>'unathorized action']],401 );

		}


		function checkToken($user_id,$token){

			if( $this->eventDB->has('user_authentication',['AND'=>['user_id'=>$user_id,'token'=>$token,'status'=>1] ]) ){
				return true;
			}else{
				return false;
			}

		}


		function inActiveAll($user_id){
			$this->eventDB->update('user_authentication',['status'=>0],['user_id'=>$user_id]);
		}



	}
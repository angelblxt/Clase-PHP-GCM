<?php

	/**
	*
	* Clase que engloba todas las funciones 
	* referentes a Google Cloud Messaging.
	*
	* @author Ángel Querol García <angelquerolgarcia@gmail.com>
	*
	*/

		class GCM
		{

			private $_apiKey = '';
			private $_url    = 'https://android.googleapis.com/gcm/send';
			private $_devices;

				/**
				*
				* Método constructor de la clase.
				*
				*/

					public function __construct( $apiKeyIntroduced )
					{

						$this->_apiKey = $apiKeyIntroduced;

					}

				/**
				*
				* Método encargado de almacenar en la Base de Datos del nuevo "usuario" GCM.
				*
				* @param string $regId String "regId" que nos mandará el teléfono.
				*
				* @return boolean TRUE o FALSE dependiendo del éxito de la operación
				*
				*/

					public function storeUser( $regId )
					{

						$comprobeRegId = Mysql::$mysql->query("SELECT id FROM gcm WHERE regId = '". $regId ."'")->num_rows;

						if( $comprobeRegId == 0 ){

							// Si no existe, añadimos el regId.

							$query = Mysql::$mysql->query("INSERT INTO gcm (regId, tiempo) VALUES ('". $regId ."', '". time() ."')");

							return ( $query )? true : false;

						}

					}

				/**
				*
				* Método encargado de meter en un array los dispositivos a los que mandar el mensaje.
				*
				* @param array $regIds Array con los "regId" a los que mandar el mensaje.
				*
				*/

					public function setDevices( $regIds )
					{

						$this->_devices = ( is_array($regIds) )? $regIds : [$regIds];

					}

				/**
				*
				* Método encargado de enviar un mensaje a los RegId especificados.
				*
				* @param string $message Mensaje que queremos enviar.
				*
				* @return string $result Resultado del CURL
				*
				*/

					public function sendNotification( $message )
					{

						if( !is_array($this->_devices) || count($this->_devices) == 0 )
							$this->setError('ERROR: No se han especificado dispositivos.');

						if( empty($this->_apiKey) )
							$this->setError('ERROR: No se ha especificado la API KEY.');

						$data = [
							'registration_ids' => $this->_devices,
							'data'             => ['message' => $message]];

						$headers = [
							'Authorization: key='. $this->_apiKey,
							'Content-Type: application/json'];

						$ch = curl_init();

						curl_setopt($ch, CURLOPT_URL, $this->_url);

						curl_setopt($ch, CURLOPT_POST, true);
						curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

						curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

						$result = curl_exec($ch);

						if( $result == false )
							$this->setError('ERROR: CURL ha fallado: '. curl_error($ch));

						curl_close($ch);

					}

				/**
				*
				* Método encargado de mostrar los errores que genera la Clase.
				*
				* @param string $error Error a mostrar.
				*
				* @return string Mostramos el error.
				*
				*/

					public function setError( $error )
					{

						echo $error;

						exit(1);

					}

		}

?>
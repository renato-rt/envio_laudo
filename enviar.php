<?php
	include_once("phpmailer/class.phpmailer.php");
	//$stconn = "host=192.168.25.232 port=5432 dbname=clinux_genesis user=dicomvix password=system98";
    $stconn = "host=localhost dbname=clinux_crd user=dicomvix password=system98";
    $db = pg_connect($stconn);

	$cd_atendimento = $_REQUEST['cd_atendimento'];
	$resposta = $_REQUEST['resposta'];
	$obs = $_REQUEST['obs'];
	$cd_resposta = $_REQUEST['cd_resp'];
	$email_paciente = $_REQUEST['email'];

	$query = "select ds_paciente from pacientes join atendimentos using(cd_paciente) where cd_atendimento = ".$cd_atendimento;
	$result = pg_query($db,$query);
	$resultado = pg_fetch_assoc($result);

	$sql = "insert into pesquisa_satisfacao(cd_atendimento,cd_resposta,ds_resposta,ds_observacao,email) values(".$cd_atendimento.",".$cd_resposta.",'".$resposta."','".$obs."','".$email_paciente."')";
	$resultAux = pg_query($db,$sql);

	if($resultAux){
	    
		if(!empty($email_paciente)){
			//Inicia a classe PHPMailer
			$mail = new PHPMailer();
			//Define os dados do servidor e tipo de conexão
			$mail->IsSMTP(); // Define que a mensagem será SMTP
			$mail->Host = 'smtp.gmail.com';//'mail.clinux.com.br'; // Endereço do servidor SMTP
			$mail->Port = '465';
			$mail->SMTPSecure = 'ssl';
			$mail->SMTPAuth = true; // Autenticação
			$mail->Username = 'crdpesquisa2@gmail.com'; // Usuário do servidor SMTP
			$mail->Password = 'crd10121314';//'System98'; // Senha da caixa postal utilizada
			//Define o remetente
			$mail->From = 'crdpesquisa2@gmail.com'; 
			$mail->FromName = 'CRD - Pesquisa de satisfação';

			//Define os destinatario(s) 
			$mail->AddAddress(strtolower($email_paciente), $resultado['ds_paciente']);

			//Define os dados tecnicos da Mensagem
			$mail->IsHTML(true); // Define que o e-mail será enviado como HTML
			$mail->CharSet = 'utf-8'; // Charset da mensagem (opcional)
			//Texto e Assunto
			$mail->Subject  = "CRD - Pesquisa de satisfação"; // Assunto da mensagem
			//$mail->Body = "Ol&aacute; <b>".$resultado['ds_paciente']."</b>, obrigado por responder a nossa pesquisa de satisfa&ccedil;&atilde;o.";
			$mail->Body = "<div style='max-width:700PX;height:auto; font-size:13px;padding:50px;text-align:center;background-color:#E4E4E4;vertical-align:middle;clear:both;font-family: NewsGoth Lt BT,'Trebuchet MS', Arial, Helvetica, sans-serif;'>
						   	<div style='text-align:center;margin-top:-20px;margin-bottom:5px;'><img src='http://crd.zapto.org/pesquisa/layout/img/crd.png'></div>
							<div style='background-color: #474787; width:670px;font-weight: bold;color: #fff;align:middle;text-align:center;font-family: NewsGoth Lt BT,Trebuchet MS,Arial;padding: 10px 20px;font-size:18px;'>
								Pesquisa de satisfa&ccedil;&atilde;o
							</div>
							<div style='background-color: #FFF; width:670px;color: #777;text-align:justify;font-family: NewsGoth Lt BT,Trebuchet MS,Arial;padding:20px;'>
								<div>
									<p>Ol&aacute; <b>".$resultado['ds_paciente']."</b>, obrigada por responder a nossa pesquisa de satisfa&ccedil;&atilde;o.<p>
									
									<p>Suas considera&ccedil;&otilde;es est&atilde;o sendo avaliadas pela equipe do CRD - MEDICINA DIAGN&Oacute;STICA</p>

									<p>Grata,</p>
									
									<p style='color:#999;'>Atenciosamente,</p>
									<div style='clear: both;border-top:1px dotted #ccc;margin-top:15px; margin-bottom:15px;'></div>
									<p style='color:#999;font-size=12px;'>
										Idalina Daher<br>
										Gestora de Relações Públicas<br>
										CRD - Medicina Diagn&oacute;stica
									</p>
								</div>
								<div style='clear: both;border-top:1px dotted #ccc;margin-top:15px; margin-bottom:15px;'></div>
							</div>
						   </div>
						   <div style='text-align:center;max-width:700px;padding:0px 50px;font-size:11px;'>***esta &eacute; uma mensagem automatica por favor n&atilde;o responda***</div>";
			//Envio da Mensagem
			$enviado = $mail->Send();
			//Limpa os destinatarios e os anexos
			$mail->ClearAllRecipients();
		}
		echo 1;
	}else{
		echo 0;
	}
?>

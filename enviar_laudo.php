<?php

session_start();
error_reporting(0);
date_default_timezone_set('America/Sao_Paulo');

include "conexao.php";
include "phpmailer/class.phpmailer.php";

//echo "aqui";
$sql = "select
	 aa.nr_controle as nr_controle,
	 pa.ds_paciente as ds_paciente,
         ex.cd_exame,
	 pa.cd_paciente,
	 ex.cd_atendimento as cd_atendimento,
	 pa.ds_email as ds_email_paciente,
 	 ms.ds_email as ds_email_medico,
	 la.dt_assinado as dt_assinado,
	 case
	 when nr_assinado > 0 
	 then blob_readfile(cd_laudo,
			    '/arquivamento/documentos/laudos_assinados' || to_char(la.dt_assinado, '/yyyy/mm/dd/'))
	   else bb_assinado end as bb_laudo,
	 encode(bb_assinado, 'base64') as bb_64,
	 ms.ds_medico
	 from
	 laudos_assinados la
	 inner join 
	 exames ex on ex.cd_exame = la.cd_laudo
	 join atendimentos aa using (cd_atendimento)
	   join pacientes pa
	 using(cd_paciente)
	   join medicos ms on
	 (ex.cd_medico = ms.cd_medico)
	     where
	 1 = 1
	 and pa.ds_email is not null
	 and la.bb_assinado is not null
	 and la.dt_assinado >  now() -interval '1 DAY'";

//cria executa a query para pegar os dados
$result = resultadoSQLAll($sql);
//conta quantos registros vieram do banco
$numerolinhas = pg_num_rows(criaQueryResult($sql));
echo "num de linhas ".$numerolinhas."\n";
//se existirem dados na tabela então envia os emails
if($numerolinhas > 0){
    foreach($result as $value){
	$email_medico="";
	$sql = "select cd_atendimento from aux_email_laudo_assinado where cd_atendimento = ".$value['cd_atendimento'];
	//cria executa a query para pegar os dados
	$result = resultadoSQLAssoc($sql);
	//conta quantos registros vieram do banco
	$num_linhas = pg_num_rows(criaQueryResult($sql));
echo "num de linhas ".$num_linhas." na tabela auxiliar\n";
	    if($num_linhas == 0){
echo "num de linhas validado\n";
echo "enviando email para ".$value['ds_email_paciente']."\n";
		if(!empty($value['ds_email_paciente'])){
//echo "Resultado ".(!empty($value['ds_email']))."\n ";
		    //Inicia a classe PHPMailer
		    $mail = new PHPMailer();
		    $mail->setLanguage('pt');
		    //Define os dados do servidor e tipo de conexão
		    $mail->IsSMTP(); // Define que a mensagem será SMTP
		    $mail->Host = 'smtp.gmail.com';//'mail.clinux.com.br'; // Endereço do servidor SMTP
		    $mail->Port = '465';
		    $mail->SMTPSecure = 'ssl';
		    $mail->SMTPAuth = true; // Autenticação
		    $mail->Username = 'crdpesquisa2@gmail.com'; // Usuário do servidor SMTP
		    $mail->Password = 'crd10121314';//'System98'; // Senha da caixa postal utilizada
		    $mail->FromName = 'crdpesquisa2@gmail.com';//Define o remetente
		    //$mail->FromName = 'CRD - Pesquisa de satisfação';
echo "Php mailer ok \n";
		    //Define os destinatario(s)
		    $mail->AddAddress(strtolower($value['ds_email_paciente']), $value['ds_email_paciente']);
		    //Anexa o Laudo
		    $mail->AddStringAttachment(base64_decode($value['bb_64']),'Laudo.pdf', 'base64','application/pdf');
echo "Laudo enviado\n";
		    //Define o destinatario do médico e envia de forma ocuta
		    if(!empty($value['ds_email_medico'])){
			$mail_medico=$value['ds_email_medico'];
			$mail->AddBCC(strtolower($value['ds_email_paciente']), $value['ds_email_medico']);
echo "Email medico, ok \n";
		    }else{
		    $email_medico="Email medico nao informado";}
		    //Define os dados tecnicos da Mensagem
		    $mail->IsHTML(true); // Define que o e-mail será enviado como HTML
		    $mail->CharSet = 'utf-8'; // Charset da mensagem (opcional)
		    $mail->Encoding = 'quoted-printable';
echo "testes....". mb_detect_encoding('tesestesç');
		    //Texto e Assunto
		    $mail->Subject  = html_entity_decode('CRD Laudos'); // Assunto da mensagem
echo "Dados Tecnicos e assunto, ok\n";		    
		    //$mail->Body = "Ol&aacute; <b>".$resultado['ds_paciente']."</b>, obrigado por responder a nossa pesquisa de satisfa&ccedil;&atilde;o.";
		    $mail->Body = "<div style='overflow:hidden;max-width:100%;height:auto; font-size:14px;padding:50px;
				  text-align:center;background-color:#E4E4E4;vertical-align:middle;clear:both;
				  font-family: NewsGoth Lt BT,'Trebuchet MS', Arial, Helvetica, sans-serif;'>
				    <div style='float:left;width:100%;background-color: #392f77;font-weight: bold;
				  color: #fff;align:middle;text-align:center;font-family: NewsGoth Lt BT,Trebuchet MS,Arial;padding: 10px 20px;
				  font-size:18px;'>
				    Laudo de Exame
				    </div>
				    <div style='clear:both'></div>
				    <div style='float:left;width:100%;background-color: #FFF; color: #777;text-align:justify;
				  font-family: NewsGoth Lt BT,Trebuchet MS,Arial;padding:20px;'>
				    <div>
				    <p><center><img src='http://crd.zapto.org/pesquisa/layout/img/crd.png'></center></p>
				    <p>Olá <b>".$value['ds_paciente']."</b>, </p>
				    <p>Seu laudo está em anexo caso queira ver demais laudos e imagens, clique no link abaixo para acessar<br/><b>".$value['dt_assinado']."</b>
				    e protocolo <b>".$value['cd_atendimento']."</><p>
				    <p><a href='https://web.clinux.com.br/portal/crd/resultados/".$value['nr_controle']."/".$value['cd_paciente']."'>
				    <b>Acessar Laudo</b></a></p>
   	      			    <p style='color:#392f77;'>
	   		     	    Atenciosamente,<br>
			       CRD - Medicina Diagn&oacute;stica
	      	   </p>
	           <div style='clear: both;border-top:1px dotted #ccc;margin-top:15px; margin-bottom:15px;'></div>
		   </div>
	           </div>
	     	   </div>
	           <div style='clear:both;text-align:center;max-width:100%;padding:0px 50px;
		   font-size:11px;'>***esta &eacute; uma mensagem autom&aacute;tica por favor n&atilde;o responda***</div>";
		    
		    //Envio da Mensagem
		    $enviado = $mail->Send();
echo "Email enviado \n".$enviado;
		    //Armazena o registro na tabela auxiliar
		    $sql = " INSERT INTO public.aux_email_laudo_assinado
			     (cd_atendimento, cd_exame, cd_paciente, ds_email_paciente, ds_email_medico,
			      dt_enviado, dt_integrado, ds_medico, sn_enviado) VALUES('"
		      .$value['cd_atendimento']."', '".$value['cd_exame']."', '".$value['cd_paciente']."','".$value['ds_email_paciente']
		      ."','".$email_medico."','now()','now()','".$value['ds_medico']."', True);";
echo $sql;
		    echo "Registro armazenado na tabela\n";
		    $resultado = criaQueryResult($sql);
		 	      
		   if($resultado){
		   $fp = fopen("log/".$value['data'].".txt", "a");
		   $texto = "Email: ".$value['ds_email'];
	       	   $texto .= "\n";
		   $escreve = fwrite($fp, (string)$texto);
	         }
		   														    
	     //Limpa os destinatarios e os anexos
	           $mail->ClearAllRecipients();
	        }
	       }
	     }
   echo 1;
      }else{
   echo 0;
  }
?>

<?php

list($diaInicio,$mesInicio,$anoInicio) = explode("/",$_POST['txtDataInicial']);
$dataInicial = $anoInicio."-".$mesInicio."-".$diaInicio;

list($diaFim,$mesFim,$anoFim) = explode("/",$_POST['txtDataFinal']);
$dataFinal = $anoFim."-".$mesFim."-".$diaFim;

$cSQL = "
  SELECT
	c.id,
	c.firstname,
	c.lastname,
	c.companyname,
        c.phonenumber,
        c.address1,
        c.address2,
	c.email,
        c.state,
        c.city,
        i.total,
        i.id as invoice_id,
        i.duedate,
        i.datepaid,
        (select value from tblcustomfieldsvalues where relid=c.id and fieldid=1 limit 1) as cpf_cnpj
  FROM tblinvoices i
  JOIN tblclients c ON(c.id = i.userid) 
  WHERE
	datepaid BETWEEN '".$dataInicial."' AND '".$dataFinal."' 
        And i.total > 0    
";
//echo $cSQL;
//die("teste");

$oSQL = mysql_query($cSQL);

echo "<br /><br />";
echo "<form method='post'>";
echo "<input type='hidden' name='dtInicial' value='".$_POST['txtDataInicial']."' />";
echo "<input type='hidden' name='dtFinal' value='".$_POST['txtDataFinal']."' />";
echo "<table border='1' style='border-collapse: collapse'>";
echo "<tr>";
echo "<th style='background-color: #E4E4E4;'><input type='checkbox' id='chkTodos' /></th>";
echo "<th style='background-color: #E4E4E4; padding: 0 5px 0 5px;'>id</th>";
echo "<th style='background-color: #E4E4E4; padding: 0 5px 0 5px;'>Nome</th>";
echo "<th style='background-color: #E4E4E4; padding: 0 5px 0 5px;'>Empresa</th>";
echo "<th style='background-color: #E4E4E4; padding: 0 5px 0 5px;'>Email</th>";
echo "<th style='background-color: #E4E4E4; padding: 0 5px 0 5px;'>Fatura</th>";
echo "<th style='background-color: #E4E4E4; padding: 0 5px 0 5px;'>Dt. Venc.</th>";
echo "<th style='background-color: #E4E4E4; padding: 0 5px 0 5px;'>Dt. Pagto.</th>";
echo "<th style='background-color: #E4E4E4; padding: 0 5px 0 5px;'>Fone</th>";
echo "<th style='background-color: #E4E4E4; padding: 0 5px 0 5px;'>Valor</th>";
echo "<th style='background-color: #E4E4E4; padding: 0 5px 0 5px;'>Error(s)</th>";
echo "</tr>";

/*
[17:30:13] Sidnei: nome
empresa
email
fatura
data venc fatura
data pagto fatura
tel
valor
*
 * 
 */
while($oSQL && $aDados = mysql_fetch_array($oSQL)) {
  echo "<tr>";
  echo "<td><input type='checkbox' name='chkCliente[]' class='chkClient' id='chkCliente_'".$aDados['invoice_id']." value='".$aDados['invoice_id']."' /></td>";
  echo "<td><a target='blank' href='clientssummary.php?userid= ".$aDados['id']."'>".$aDados['id']."</td>";
  echo "<td style='padding: 0 5px 0 5px;'>".$aDados['firstname'] . " " . $aDados['lastname'] ."</td>";
  echo "<td style='padding: 0 5px 0 5px;'>".$aDados['companyname']."</td>";
  echo "<td style='padding: 0 5px 0 5px;'>".$aDados['email']."</td>";
  echo "<td style='padding: 0 5px 0 5px;'>".$aDados['invoice_id']."</td>";
  echo "<td style='padding: 0 5px 0 5px;'>".date("d/m/Y", strtotime($aDados['duedate']))."</td>";
  echo "<td style='padding: 0 5px 0 5px;'>".date("d/m/Y", strtotime($aDados['datepaid']))."</td>";
  echo "<td style='padding: 0 5px 0 5px;'>".$aDados['phonenumber']."</td>";
  echo "<td style='padding: 0 5px 0 5px;'>".number_format($aDados['total'],2,",",".")."</td>";
  echo "<td style='padding: 0 5px 0 5px;'>";
  showErrors($aDados,$aVars);
  echo "</td>";
  echo "</tr>";
  $total += $aDados['total'];
}

echo "<tr>";

echo "<td colspan='9' align='right'>Total</td>";
echo "<td>";
echo number_format($total,2,",",".");
echo "</td>";
echo "<td></td>";
echo "</tr>";

echo "</table>";

echo "<br />";
echo "<input type='hidden' name='export' value='1'/> ";
echo "<input type='submit' id='btnGerarArquivo' value='Gerar Arquivo' /> ";
echo "<input type='checkbox' value='1' name='debug'/>modo debug";

echo "</form>";
	
?>

<script>
  
    $(document).ready(function(){
        $("#chkTodos").click(function(){
            if($(this).attr('checked'))
                $(".chkClient").attr('checked',true);
            else
                $(".chkClient").attr('checked',false);
        });
    })

</script>
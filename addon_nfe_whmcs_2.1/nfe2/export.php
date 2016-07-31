<?php
error_reporting(E_ALL ^ E_NOTICE);

function percentage($val1, $val2, $precision) 
{
    $division = $val1 * $val2;

    $res = $division / 100;

    $res = round($res, $precision);

    return $res;
}

list($diaInicio,$mesInicio,$anoInicio) = explode("/",$_POST['dtInicial']);
$dataInicial = $anoInicio."-".$mesInicio."-".$diaInicio;

list($diaFim,$mesFim,$anoFim) = explode("/",$_POST['dtFinal']);
$dataFinal = $anoFim."-".$mesFim."-".$diaFim;

if(!isset($_POST['chkCliente']))
{
    echo("Selecione pelo menos uma fatura para gerar o arquivo!");
}    
else
{
$ids = implode(",",$_POST['chkCliente']);

$query = "
  SELECT
	c.id,
	c.firstname,
	c.lastname,
	c.companyname,
        c.phonenumber,
        c.postcode,
	c.email,
	c.address1,
        c.address2,
        c.state,
        i.total,
        i.id as iid
  FROM tblinvoices i      
  JOIN tblclients c ON(c.id = i.userid)
  WHERE
	datepaid BETWEEN '".$dataInicial."' AND '".$dataFinal."'
	and i.id in ({$ids})
";

$result = mysql_query($query);


$linhas = array();
$linhas[] = "0|1.06"; // Cabeçalho

//debug
//$row = mysql_fetch_array($result);
//var_dump($row);

define("MAX_INVOICES_PER_FILE",100);

$count_invoices = 0;

$txt_files = array();

$lote_rps = (int)$aVars['lote_rps'];

while($row = mysql_fetch_array($result))
{
    $linha = array();

    $custom_fields = getClientCustomFields($row["id"]);
    
    $linha["Identificador"] = "1"; //Ok
    $linha["lote_rps"] = $lote_rps ; 
    $linha["cnpj"] = $aVars['cnpj']; //OK
    $linha["inscricao_municipal"] = $aVars['inscricao_municipal']; //OK
    $linha["es_municipio"] = $aVars['es_municipio']; //OK
    if($_POST['debug'])
        $linha["numero"] = $row["iid"] . $lote_rps;
    else
        $linha["numero"] = $row["iid"];
    
    $linha["serie"] = $aVars['serie']; //OK
    $linha["dt_emissao"] = date('Y-m-d'); //OK                 
    $linha["cd_natureza_operacao"] = $aVars['cd_natureza_operacao'];
    $linha["cd_regime_especial_tributacao"] = $aVars['cd_regime_especial_tributacao'];
    $linha["sn_optante_simples_nacional"] = $aVars['sn_optante_simples_nacional']=='Sim'?1:2;
    $linha["cd_status"] = $aVars['cd_status']=='Ativa'?1:2;
    $linha["es_item_lista_servico"] = $aVars['es_item_lista_servico'];
    $linha["cd_tributacao_municipio"] = $aVars['cd_tributacao_municipio'];
    $linha["es_cnae"] = $aVars['es_cnae'];
    $linha["discriminacao"] = $aVars['discriminacao'];
    $linha["vl_servicos"] = $row["total"];       // Valor total do serviço
    $linha["vl_deducao"] = percentage($row["total"],$aVars['vl_deducao'],2);
    $linha["vl_pis"] = percentage($row["total"],$aVars['vl_pis'],2); 
    $linha["vl_cofins"] = percentage($row["total"],$aVars['vl_cofins'],2);    
    $linha["vl_inss"] = percentage($row["total"],$aVars['vl_inss'],2);
    $linha["vl_ir"] = percentage($row["total"],$aVars['vl_ir'],2);
    $linha["vl_cs"] = percentage($row["total"],$aVars['vl_cs'],2);
    $linha["sn_iss_retido"] = $aVars['sn_iss_retido']=='Sim'?1:2;       
    $linha["vl_iss"] = percentage($row["total"],number_format($aVars['vl_aliquota'],4),2);
    $linha["vl_iss_retido"] = percentage($row["total"],$aVars['vl_iss_retido'],2);
    $linha["vl_outras_retencoes"] = $aVars['vl_outras_retencoes'];   
    $linha["vl_base_calculo"] = number_format($row["total"] - $linha["vl_deducao"],2);   
    $linha["vl_aliquota"] = number_format($aVars['vl_aliquota'],4);
    $vl_liquido_nfse = $aVars["vl_servicos"] - ($aVars["vl_pis"] + $aVars["vl_cofins"] + $aVars["vl_inss"] + $aVars["vl_ir"] + $aVars["vl_cs"] + $aVars["vl_outras_retencoes"] + $aVars["vl_iss_retido"]  +  $aVars["vl_desconto_incondicionado"] + $aVars["vl_desconto_condicionado"]);
    
    //if($discount > 0)
        //die("Desconto: " . $discount);
    
    $linha["vl_liquido_nfse"] = number_format($row["total"] - $vl_liquido_nfse ,2);   
    $linha["vl_desconto_incondicionado"] = percentage($row["total"],$aVars['vl_desconto_incondicionado'],2);    
    $linha["vl_desconto_condicionado"] = percentage($row["total"],$aVars['vl_desconto_condicionado'],2);

    //Corrção ISS
    //$linha["vl_iss"] = percentage($linha["vl_aliquota"],$aVars['vl_iss'],2);
    
    if(strlen($custom_fields[$aVars['tom_cpf_cnpj_field_name']])==11)
        $linha["tom_cd_cpf_cnpj_tipo"] = 1; 
    else
        $linha["tom_cd_cpf_cnpj_tipo"] = 2; 

    $linha["tom_cpf_cnpj"] = removeNonNumeric($custom_fields[$aVars['tom_cpf_cnpj_field_name']]); 
    $linha["tom_inscricao_municipal"] = ''; 
    
    if(strlen($custom_fields[$aVars['tom_cpf_cnpj_field_name']])==11)
        $linha["tom_razao_social"] = $row["firstname"] . " " .  $row["lastname"]; 
    else
        $linha["tom_razao_social"] = $row["companyname"]; 
        
    
    //Ex: Rua Darabi,37,Vila Inglesa,SP
    $address = explode(",",$row["address1"]);
    
    $linha["tom_endereco"] = trim($address[0]); 
    $linha["tom_endereco_numero"] = trim($address[1]);
    $linha["tom_endereco_complemento"] = ""; // Não obrigatório
    $linha["tom_endereco_bairro"] = trim($row["address2"]);
    $linha["tom_endereco_uf"] = formatUF($row["state"]);
    $linha["tom_endereco_cep"] = removeNonNumeric($row["postcode"]);// Não obrigatório
    
    $linha["tom_endereco_es_municipio"] = $custom_fields[$aVars['tom_endereco_es_municipio_field_name']]; 
    
    $phonenumber = removeNonNumeric($row["phonenumber"]);
    if(strlen($phonenumber) == 12 && substr($phonenumber,0,2)=="55")
       $phonenumber = substr($phonenumber,2);
    
    $linha["tom_telefone"] = $phonenumber; //OK
    $linha["tom_email"] = $row["email"]; //OK

    $linha["intermediario_razao_social"] = ''; // Não obrigatório
    $linha["intermediario_cpf_cnpj"] = ''; // Não obrigatório
    $linha["intermediario_cd_cpf_cnpj_tipo"] = ''; // Não obrigatório
    $linha["intermediario_inscricao_municipal"] = ''; // Não obrigatório
    $linha["construcao_civil_cd_obra"] = ''; // Não obrigatório
    $linha["construcao_civil_cd_art"] = ''; // Não obrigatório
    $linha["numero_substituta"] = ''; // Não obrigatório
    $linha["serie_substituta"] = ''; // Não obrigatório
    $linha["orgao_gerador_es_municipio"] = $aVars['es_municipio'];
    $linha["orgao_gerador_uf"] = $aVars['uf'];
    $linha["outras_informacoes"] = '';
    $linha["tom_inscricao_estadual"] = ''; // Não obrigatório

    $linhas[] = implode("|",$linha);

    $discount = getDiscountItens($row["iid"]);
    if(!$discount)
    {    
        $query = "select * from tblinvoiceitems where invoiceid=". $row["iid"];
        $result2 = mysql_query($query);

        while($row2 = mysql_fetch_array($result2))
        {
            if($row2["amount"] > 0)
            {
                $linha = array();

                $desc = str_replace(array("\n","\r"),"-",$row2["description"]);

                $linha["identificador"] = 2;
                $linha["descricao"] = substr($desc,0,100);
                $linha["qt_item"] = "1.00000"; // Não tem na tabela
                $linha["vl_unitario"] = $row2["amount"] . "00";
                $linha["sn_iss_tributavel"] = '1'; //1 = Sim

                $linhas[] = implode("|",$linha);
            }

        }
    }
    else
    {
        $linha = array();

        $desc = str_replace(array("\n","\r"),"-",$row2["description"]);

        $linha["identificador"] = 2;
        $linha["descricao"] = $aVars['discriminacao'] . " Ref Fatura #" . $row["iid"];
        $linha["qt_item"] = "1.00000"; // Não tem na tabela
        $linha["vl_unitario"] = $row["total"] . "00";
        $linha["sn_iss_tributavel"] = '1'; //1 = Sim

        $linhas[] = implode("|",$linha);     
    }
    
    $count_invoices++;
    
    
    if($count_invoices >= MAX_INVOICES_PER_FILE)
    {
        //Salta arquivo/lote rps
        $file = "lote-" . $lote_rps . ".txt";
        $txt_files[] = $file;        
        file_put_contents("../modules/addons/nfe2/lotes/{$file}", implode("|\r\n",$linhas) ."|");
        // Incrementa Nº do lote RPS e salva nas configurações
        $lote_rps++;
        setAddonParameter("lote_rps",$lote_rps);
        // Zera linhas para o proximo arquivo
        $linhas = array();
        $linhas[] = "0|1.06"; // novo Cabeçalho
        //Zera contador
        $count_invoices =0;
    }
    
}

if(!empty($linhas))
{
    //Salta arquivo/lote rps
    $file = "lote-" . $lote_rps . ".txt";
    $txt_files[] = $file;        
    file_put_contents("../modules/addons/nfe2/lotes/{$file}", implode("|\r\n",$linhas) ."|");
    // Incrementa Nº do lote RPS e salva nas configurações
    $lote_rps++;
    setAddonParameter("lote_rps",$lote_rps);
}


//Exporta em formato txt para download
/*
header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
header ("Cache-Control: no-cache, must-revalidate");
header ("Pragma: no-cache");
header ("Content-Type: text/plain;");
header ("Content-Disposition: attachment; filename=\"relatorio-" . uniqid() . ".txt\"" );
header ("Content-Description: PHP Generated Data" );
header ("Content-Transfer-Encoding: binary");
*/

echo "<p><b>Arquivo(s) gerado(s) com sucesso</b><p>";

foreach($txt_files as $file)
{
    echo "<a href='../modules/addons/nfe2/download.php?filename={$file}'>{$file}</a><br>";
}
}


function setAddonParameter($param,$value)
{
    $q = "update tbladdonmodules set value='{$value}' where module = 'nfe2' and setting='{$param}'";
    $result = mysql_query($q);
    return true;
}


function getDiscountItens($id)
{
    $query = "select abs(sum(amount)) as tot from tblinvoiceitems where invoiceid={$id} and amount <=0";
    $result = mysql_query($query);
    $row = mysql_fetch_array($result); 
    return $row["tot"];
}
?>
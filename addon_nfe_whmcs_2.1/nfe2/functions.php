<?php

function getClientCustomFields($id)
{
    $q = "select cfv.value,cf.fieldname from tblcustomfieldsvalues cfv join tblcustomfields cf on cf.id=cfv.fieldid where cfv.relid={$id}";
    $result = mysql_query($q );
    $fields = array();
    while($row = mysql_fetch_array($result))
    {
        $fields[$row["fieldname"]] = $row["value"];
    }

    return $fields;
}

function removeNonNumeric($value)
{
    return preg_replace("/[^0-9]/","",$value);
}

function formatUF($value)
{
    if(strlen($value)==2)
        return strtoupper($value);

    switch(strtoupper($value))
    {
        case "ACRE":
            return "AC";
        case "ALAGOAS":
            return "AL";
        case "AMAZONAS":
            return "AM";
        case "AMAPA":
            return "AP";
        case "BAHIA":
            return "BA";
        case "CEARA":
            return "CE";
        case "DISTRITO FEDERAL":
            return "DF";
        case "ESPIRITO SANTO":
            return "ES";
        case "GOIAS":    
            return "GO";
        case "MACAPA":
            return "MA";
        case "MINAS GERAIS":
            return "MG";
        case "MATO GROSSO DO SUL":    
            return "MS";
        case "MATO GROSSO":
            return "MT";
        case "PARANA":
            return "PA";
        case "PARAIBA":
            return "PB";
        case "PERNAMBUCO":
            return "PE";
        case "PIAUI":
            return "PI";
        case "PERNAMBUCO":
            return "PR";
        case "RIO DE JANEIRO":
            return "RJ";
        case "RIO GRANDE DO NORTE":
            return "RN";
        case "RONDONIA":            
            return "RO";
        case "RORAIMA":            
            return "RR";
        case "RIO GRANDE DO SUL":            
            return "RS";
        case "SANTA CATARINA":            
            return "SC";
        case "SERGIPE":            
            return "SE";
        case "SÃO PAULO":
        case "SAO PAULO":
            return "SP";
        case "TOCANTINS":            
            return "TO";            
        default:
            return "ERRO";
    }
}

function validaCPF($cpf)
{	// Verifiva se o número digitado contém todos os digitos
    $cpf = str_pad(ereg_replace('[^0-9]', '', $cpf), 11, '0', STR_PAD_LEFT);
	
	// Verifica se nenhuma das sequências abaixo foi digitada, caso seja, retorna falso
    if (strlen($cpf) != 11 || $cpf == '00000000000' || $cpf == '11111111111' || $cpf == '22222222222' || $cpf == '33333333333' || $cpf == '44444444444' || $cpf == '55555555555' || $cpf == '66666666666' || $cpf == '77777777777' || $cpf == '88888888888' || $cpf == '99999999999')
	{
	return false;
    }
	else
	{   // Calcula os números para verificar se o CPF é verdadeiro
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf{$c} * (($t + 1) - $c);
            }

            $d = ((10 * $d) % 11) % 10;

            if ($cpf{$c} != $d) {
                return false;
            }
        }

        return true;
    }
}

function validaCNPJ($cnpj)
{
    $cnpj = preg_replace ("@[./-]@", "", $cnpj);
    if (strlen ($cnpj) <> 14 or !is_numeric ($cnpj))
    {
    return 0;
    }
    $j = 5;
    $k = 6;
    $soma1 = "";
    $soma2 = "";

    for ($i = 0; $i < 13; $i++)
    {
    $j = $j == 1 ? 9 : $j;
    $k = $k == 1 ? 9 : $k;
    $soma2 += ($cnpj{$i} * $k);

    if ($i < 12)
    {
    $soma1 += ($cnpj{$i} * $j);
    }
    $k--;
    $j--;
    }

    $digito1 = $soma1 % 11 < 2 ? 0 : 11 - $soma1 % 11;
    $digito2 = $soma2 % 11 < 2 ? 0 : 11 - $soma2 % 11;
    return (($cnpj{12} == $digito1) and ($cnpj{13} == $digito2));
}


function showErrors($row,$aVars)
{
    
    $custom_fields = getClientCustomFields($row["id"]);
    
    echo "<div style='color: red;'>";

    if(empty($row['city']))
        echo "- Campo cidade vazio<br>";
  
    if(formatUF($row['state'])=="ERRO")
        echo "- UF invalida (".$row['state'].")<br>";
  
    $cpf_cnpj = removeNonNumeric($row['cpf_cnpj']);
    
    if(strlen($cpf_cnpj)!=11 && strlen($cpf_cnpj)!=14)
        echo "- CPF ou CNPJ inválido ($cpf_cnpj)<br>";
    
    $tom_endereco_es_municipio = trim($custom_fields[$aVars['tom_endereco_es_municipio_field_name']]); 
    
    if((strlen($cpf_cnpj)==14) && (strlen($tom_endereco_es_municipio) != 7))
        echo "- Código do tomador do municipio invalido ({$tom_endereco_es_municipio})<br>";
    
    if((strlen($cpf_cnpj)==11) && validaCPF($cpf_cnpj)==false)
        echo "- CPF invalido ({$cpf_cnpj})<br>";
        
    if((strlen($cpf_cnpj)==14) && validaCNPJ($cpf_cnpj)==false)
        echo "- CNPJ invalido ({$cpf_cnpj})<br>";

    if((strlen($cpf_cnpj)==14) && trim($row["companyname"])=="")
        echo "- Razão social invalida (".$row["companyname"].")<br>";

    $phonenumber = removeNonNumeric($row['phonenumber']);
    
    if(strlen($phonenumber) == 12 && substr($phonenumber,0,2)=="55")
       $phonenumber = substr($phonenumber,2);
        
    if(strlen($phonenumber) > 11)
       echo "- Telefone invalido ($phonenumber)<br>";
            
    $address = explode(",",$row["address1"]);
    if(!is_numeric(trim($address[1])))// Numero
        echo "- Numero do endereço inválido<br>";

    if(str_word_count($row["address2"]) > 3)// Numero
        echo "- Verificar 'endereço 2' deve ser o bairro";
    

    echo "</div>";
    
}


?>

<!-- <?php 
// use Adianti\Database\TRecord;

// class Pessoa extends TRecord
// {
//     const TABLENAME = 'pessoa';

//     public function validate()
//     {
//         if ($this->tipo === 'Físico' && empty($this->cpf)){
//             throw new Exception('O CPF é obrigatório para Pessoa Física');
//         }

//         if ($this->tipo === 'Jurídico' && empty($this->cnpj)){
//             throw new Exception('O CNPJ é obrigatório para Pessoa Jurídica');
//         }

//         if (!empty($this->cpf) && !self::isValidCPF($this->cpf)){
//             throw new Exception('CPF inválido');
//         }

//         if (!empty($this->cnpj) && !self::isValidCNPJ($this->cnpj)){
//             throw new Exception('CNPJ inválido');
//         }
//     }

//     private static function isValidCPF($cpf){
//         $cpf = preg_replace('/\D/', '', $cpf);

//         if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)){
//             return false;
//         }

//         for ($t = 9; $t < 11; $t++){
//             $sum = 0;
//             for ($i = 0; $i < $t; $i++){
//                 $sum += $cpf[$i] * (($t + 1) - $i);
//             }
//             $digit = (10 * $sum) % 11 % 10;
//             if ($cpf[$t] != $digit){
//                 return false;
//             }
//         }

//         return true;
//     }

//     private static function isValidCNPJ($cnpj){
//         $cnpj = preg_replace('/\D/', '', $cnpj);

//         if (strlen($cnpj) != 14 || preg_match('/(\d)\1{13}/', $cnpj)){
//             return false;
//         }

//         $weights1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
//         $weights2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

//         $sum = 0;
//         for ($i = 0; $i < 12; $i++){
//             $sum += $cnpj[$i] * $weights1[$i];
//         }
//         $digit1 = ($sum % 11) < 2 ? 0 : 11 - ($sum % 11);

//         $sum = 0;
//         for ($i = 0; $i < 13; $i++){
//             $sum += $cnpj[$i] * $weights2[$i];
//         }
//         $digit2 = ($sum % 11) < 2 ? 0 : 11 - ($sum % 11);

//         return $cnpj[12] == $digit1 && $cnpj[13] == $digit2;
//     }
// }
?> -->

<?php
use Adianti\Database\TRecord;

class Pessoa extends TRecord
{
    const TABLENAME = 'pessoa';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // max, serial

    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('tipo');
        parent::addAttribute('nome_completo');
        parent::addAttribute('razao_social');
        parent::addAttribute('cpf');
        parent::addAttribute('cnpj');
        parent::addAttribute('email');
        parent::addAttribute('telefone');
        parent::addAttribute('rua');
        parent::addAttribute('numero');
        parent::addAttribute('complemento');
        parent::addAttribute('bairro');
        parent::addAttribute('cidade');
        parent::addAttribute('estado');
        parent::addAttribute('cep');
        parent::addAttribute('data_cadastro');
    }

    public function validate()
    {
        // Validação de campos obrigatórios
        if (empty($this->tipo)) {
            throw new Exception("O campo Tipo é obrigatório");
        }
        if (empty($this->email)) {
            throw new Exception("O campo E-mail é obrigatório");
        }
        
        // Validação específica por tipo
        if ($this->tipo == 'Físico') {
            if (empty($this->nome_completo)) {
                throw new Exception("O campo Nome Completo é obrigatório para Pessoa Física");
            }
            if (empty($this->cpf)) {
                throw new Exception("O campo CPF é obrigatório para Pessoa Física");
            }
            if (!$this->validaCPF($this->cpf)) {
                throw new Exception("CPF inválido");
            }
        } else {
            if (empty($this->razao_social)) {
                throw new Exception("O campo Razão Social é obrigatório para Pessoa Jurídica");
            }
            if (empty($this->cnpj)) {
                throw new Exception("O campo CNPJ é obrigatório para Pessoa Jurídica");
            }
            if (!$this->validaCNPJ($this->cnpj)) {
                throw new Exception("CNPJ inválido");
            }
        }

        if (!empty($this->telefone)) {
            if (!$this->validaTelefone($this->telefone)) {
                throw new Exception("O número de telefone não é válido");
            }
        }
    }

    private function validaCPF($cpf) {
        $cpf = preg_replace('/\D/', '', $cpf);

        if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)){
            return false;
        }

        for ($t = 9; $t < 11; $t++){
            $sum = 0;
            for ($i = 0; $i < $t; $i++){
                $sum += $cpf[$i] * (($t + 1) - $i);
            }
            $digit = (10 * $sum) % 11 % 10;
            if ($cpf[$t] != $digit){
                return false;
            }
        }

        return true;
    }

    private function validaCNPJ($cnpj) {
        $cnpj = preg_replace('/\D/', '', $cnpj);

        if (strlen($cnpj) != 14 || preg_match('/(\d)\1{13}/', $cnpj)){
            return false;
        }

        $weights1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $weights2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        $sum = 0;
        for ($i = 0; $i < 12; $i++){
            $sum += $cnpj[$i] * $weights1[$i];
        }
        $digit1 = ($sum % 11) < 2 ? 0 : 11 - ($sum % 11);

        $sum = 0;
        for ($i = 0; $i < 13; $i++){
            $sum += $cnpj[$i] * $weights2[$i];
        }
        $digit2 = ($sum % 11) < 2 ? 0 : 11 - ($sum % 11);

        return $cnpj[12] == $digit1 && $cnpj[13] == $digit2;
    }

    public function validaTelefone($telefone)
    {
        $apiKey = '87126da828035335300f4ca3c70bc6cb'; // Substitua pela sua chave da API Numverify
        $telefone = preg_replace('/[^0-9]/', '', $telefone);
        $url = "http://apilayer.net/api/validate?access_key={$apiKey}&number={$telefone}&country_code=BR&format=1";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
        curl_close($ch);

        $result = json_decode($output, true);

        return $result['valid'] ?? false;
    }
}


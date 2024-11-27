<?php
use Adianti\Control\TPage;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Core\AdiantiCoreApplication;
use Adianti\Widget\Form\TLabel;
use Adianti\Wrapper\BootstrapFormBuilder;
use Adianti\Control\TAction;
use Adianti\Widget\Util\TXMLBreadCrumb;
use  Adianti\Widget\container\TTable;

class VerificacaoTelefone extends TPage
{
    private $form;
    private $table;

    public function __construct()
    {
        parent::__construct();

        $this->form = new BootstrapFormBuilder('form_verifica_telefone');
        $this->form->setFormTitle('Verificação de Telefone');

        $telefone = new TEntry('telefone');
        $telefone->setMask('(99) 99999-9999');
        $telefone->setProperty('placeholder', '(XX) XXXXX-XXXX');

        $this->form->addFields([new TLabel('Telefone')], [$telefone]);

        $btn = $this->form->addAction('Verificar', new TAction([$this, 'onVerificar']), 'fa:check-circle');
        $btn->class = 'btn btn-sm btn-primary';

        $this->table = new TTable();
        $this->table->width = '100%';
        $this->table->addRowSet('Número', 'Validade', 'Operadora', 'Tipo');

        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add($this->table);

        parent::add($container);
    }

    public function onVerificar($param)
    {
        try
        {
            $data = $this->form->getData();
            $telefone = preg_replace('/[^0-9]/', '', $data->telefone);

            $apiKey = '87126da828035335300f4ca3c70bc6cb';
            $url = "http://apilayer.net/api/validate?access_key={$apiKey}&number={$telefone}&country_code=BR&format=1";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($ch);
            curl_close($ch);

            $result = json_decode($output, true);

            $validade = $result['valid'] ? 'Válido' : 'Inválido';
            $operadora = $result['carrier'] ?? 'Desconhecida';
            $tipo = $result['line_type'] ?? 'Desconhecido';

            $this->table->addRowSet($data->telefone, $validade, $operadora, $tipo);

            $this->form->setData($data); 
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
        }
    }
}

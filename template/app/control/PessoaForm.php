<?php
use Adianti\Control\TPage;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TRadioGroup;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Container\TVBox;
use Adianti\Core\AdiantiCoreApplication;
use Adianti\Database\TTransaction;
use Adianti\Widget\Base\TScript;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Wrapper\BootstrapFormBuilder;
use Adianti\Control\TAction;
use Adianti\Widget\Util\TXMLBreadCrumb;

class PessoaForm extends TPage
{
    protected $form;

    public function __construct()
    {
        parent::__construct();

        $this->form = new BootstrapFormBuilder('form_pessoa');
        $this->form->setFormTitle('Cadastro de Pessoa');

        // Criando os campos do formulário
        $id = new TEntry('id');
        $tipo = new TRadioGroup('tipo');
        $nome_completo = new TEntry('nome_completo');
        $razao_social = new TEntry('razao_social');
        $cpf = new TEntry('cpf');
        $cnpj = new TEntry('cnpj');
        $email = new TEntry('email');
        $telefone = new TEntry('telefone');
        $rua = new TEntry('rua');
        $numero = new TEntry('numero');
        $complemento = new TEntry('complemento');
        $bairro = new TEntry('bairro');
        $cidade = new TEntry('cidade');
        $estado = new TEntry('estado');
        $cep = new TEntry('cep');

        // Configurando os campos
        $id->setEditable(FALSE);
        $tipo->addItems(['Físico' => 'Pessoa Física', 'Jurídico' => 'Pessoa Jurídica']);
        $tipo->setLayout('horizontal');
        $tipo->setUseButton();
        $cpf->setMask('999.999.999-99');
        $cnpj->setMask('99.999.999/9999-99');
        $telefone->setMask('(99) 99999-9999');
        $cep->setMask('99999-999');

        // Adicionando os campos ao formulário
        $this->form->addFields([new TLabel('ID')], [$id]);
        $this->form->addFields([new TLabel('Tipo')], [$tipo]);
        $this->form->addFields([new TLabel('Nome Completo')], [$nome_completo]);
        $this->form->addFields([new TLabel('Razão Social')], [$razao_social]);
        $this->form->addFields([new TLabel('CPF')], [$cpf]);
        $this->form->addFields([new TLabel('CNPJ')], [$cnpj]);
        $this->form->addFields([new TLabel('E-mail')], [$email]);
        $this->form->addFields([new TLabel('Telefone')], [$telefone]);
        $this->form->addFields([new TLabel('Rua')], [$rua]);
        $this->form->addFields([new TLabel('Número')], [$numero]);
        $this->form->addFields([new TLabel('Complemento')], [$complemento]);
        $this->form->addFields([new TLabel('Bairro')], [$bairro]);
        $this->form->addFields([new TLabel('Cidade')], [$cidade]);
        $this->form->addFields([new TLabel('Estado')], [$estado]);
        $this->form->addFields([new TLabel('CEP')], [$cep]);

        // Adicionando as ações do formulário
        $btn = $this->form->addAction('Salvar', new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink('Limpar', new TAction([$this, 'onClear']), 'fa:eraser red');

        // Criando o container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);

        parent::add($container);

        // Adicionando o script para controlar a visibilidade dos campos
        TScript::create("
            function updateFieldsVisibility() {
                var tipo = $(\"input[name='tipo']:checked\").val();
                if (tipo == 'Físico') {
                    $('[name=\"nome_completo\"]').closest('.form-group').show();
                    $('[name=\"cpf\"]').closest('.form-group').show();
                    $('[name=\"razao_social\"]').closest('.form-group').hide();
                    $('[name=\"cnpj\"]').closest('.form-group').hide();
                    $('[name=\"razao_social\"]').val('');
                    $('[name=\"cnpj\"]').val('');
                } else if (tipo == 'Jurídico') {
                    $('[name=\"nome_completo\"]').closest('.form-group').hide();
                    $('[name=\"cpf\"]').closest('.form-group').hide();
                    $('[name=\"razao_social\"]').closest('.form-group').show();
                    $('[name=\"cnpj\"]').closest('.form-group').show();
                    $('[name=\"nome_completo\"]').val('');
                    $('[name=\"cpf\"]').val('');
                }
            }
            $('input[name=\"tipo\"]').change(updateFieldsVisibility);
            updateFieldsVisibility();
        ");
    }

    public function onSave($param)
    {
        try
        {
            TTransaction::open('sample');
            
            $this->form->validate();
            $data = $this->form->getData();
            
            $pessoa = new Pessoa;
            $pessoa->fromArray((array) $data);
            
            // Validação do telefone
            if (!empty($data->telefone)) {
                if (!$pessoa->validaTelefone($data->telefone)) {
                    throw new Exception("O número de telefone não é válido");
                }
            }
            
            // Validações específicas por tipo de pessoa
            if ($data->tipo == 'Físico') {
                if (empty($data->nome_completo)) {
                    throw new Exception("O campo Nome Completo é obrigatório para Pessoa Física");
                }
                if (empty($data->cpf)) {
                    throw new Exception("O campo CPF é obrigatório para Pessoa Física");
                }
                $pessoa->razao_social = null;
                $pessoa->cnpj = null;
            } else if ($data->tipo == 'Jurídico') {
                if (empty($data->razao_social)) {
                    throw new Exception("O campo Razão Social é obrigatório para Pessoa Jurídica");
                }
                if (empty($data->cnpj)) {
                    throw new Exception("O campo CNPJ é obrigatório para Pessoa Jurídica");
                }
                $pessoa->nome_completo = null;
                $pessoa->cpf = null;
            }
            
            $pessoa->store();

            $this->form->setData($pessoa);

            new TMessage('info', 'Registro salvo com sucesso');
            
            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    public function onClear($param)
    {
        $this->form->clear();
        TScript::create("updateFieldsVisibility();");
    }

    public function onEdit($param)
    {
        try
        {
            if (isset($param['key']))
            {
                $key = $param['key'];
                TTransaction::open('sample');
                $pessoa = new Pessoa($key);
                $this->form->setData($pessoa);
                TTransaction::close();
                TScript::create("updateFieldsVisibility();");
            }
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}


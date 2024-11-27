<?php
use Adianti\Control\TPage;
use Adianti\Widget\Container\TPanelGroup;
use Adianti\Widget\Container\TVBox;
use Adianti\Widget\Datagrid\TDataGrid;
use Adianti\Widget\Datagrid\TDataGridColumn;
use Adianti\Widget\Datagrid\TDataGridAction;
use Adianti\Widget\Form\TEntry;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Form\TForm;
use Adianti\Widget\Form\TRadioGroup;
use Adianti\Widget\Wrapper\TDBUniqueSearch;
use Adianti\Wrapper\BootstrapDatagridWrapper;
use Adianti\Wrapper\BootstrapFormBuilder;
use Adianti\Database\TTransaction;
use Adianti\Database\TRepository;
use Adianti\Database\TFilter;
use Adianti\Database\TCriteria;
use Adianti\Base\TStandardList;
use Adianti\Control\TAction;
use Adianti\Widget\Util\TXMLBreadCrumb;
use Adianti\Database\TExpression;
use Adianti\Base;
use Adianti\Widget\Dialog\TMessage;
use Adianti\Widget\Dialog\TQuestion;

class PessoaList extends TPage
{
    protected $form;
    protected $datagrid;
    protected $pageNavigation;

    public function __construct()
    {
        parent::__construct();

        // Cria o formulário de busca
        $this->form = new BootstrapFormBuilder('form_search_pessoa');
        $this->form->setFormTitle('Pesquisar Pessoas');

        $tipo = new TRadioGroup('tipo');
        $nome = new TEntry('nome');

        $tipo->addItems(['Físico' => 'Pessoa Física', 'Jurídico' => 'Pessoa Jurídica']);
        $tipo->setLayout('horizontal');
        $tipo->setUseButton();

        $this->form->addFields([new TLabel('Tipo')], [$tipo]);
        $this->form->addFields([new TLabel('Nome/Razão Social')], [$nome]);

        $this->form->addAction('Pesquisar', new TAction([$this, 'onSearch']), 'fa:search');
        $this->form->addAction('Novo', new TAction(['PessoaForm', 'onEdit']), 'fa:plus green');

        // Cria a datagrid
        $this->datagrid = new BootstrapDatagridWrapper(new TDataGrid);
        $this->datagrid->style = 'width: 100%';

        $column_id = new TDataGridColumn('id', 'ID', 'center', '10%');
        $column_tipo = new TDataGridColumn('tipo', 'Tipo', 'left');
        $column_nome = new TDataGridColumn('nome_completo', 'Nome/Razão Social', 'left');
        $column_email = new TDataGridColumn('email', 'E-mail', 'left');

        $this->datagrid->addColumn($column_id);
        $this->datagrid->addColumn($column_tipo);
        $this->datagrid->addColumn($column_nome);
        $this->datagrid->addColumn($column_email);

        $action_edit = new TDataGridAction(['PessoaForm', 'onEdit'], ['id'=>'{id}']);
        $action_delete = new TDataGridAction([$this, 'onDelete'], ['id'=>'{id}']);

        $this->datagrid->addAction($action_edit, 'Editar', 'fa:edit blue');
        $this->datagrid->addAction($action_delete, 'Excluir', 'fa:trash red');

        $this->datagrid->createModel();

        $panel = new TPanelGroup;
        $panel->add($this->datagrid);

        $vbox = new TVBox;
        $vbox->style = 'width: 100%';
        $vbox->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $vbox->add($this->form);
        $vbox->add($panel);

        parent::add($vbox);
    }

    public function onSearch($param)
    {
        $data = $this->form->getData();

        try
        {
            TTransaction::open('sample');

            $repository = new TRepository('Pessoa');
            $criteria = new TCriteria;

            if ($data->tipo)
            {
                $criteria->add(new TFilter('tipo', '=', $data->tipo));
            }

            if ($data->nome)
            {
                $criteria->add(new TFilter('nome_completo', 'like', "%{$data->nome}%"));
                $criteria->add(new TFilter('razao_social', 'like', "%{$data->nome}%"), TExpression::OR_OPERATOR);
            }

            $objects = $repository->load($criteria);

            $this->datagrid->clear();
            if ($objects)
            {
                foreach ($objects as $object)
                {
                    $this->datagrid->addItem($object);
                }
            }

            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    public function onDelete($param)
    {
        $action = new TAction([$this, 'Delete']);
        $action->setParameters($param);
        new TQuestion('Deseja realmente excluir o registro?', $action);
    }

    public function Delete($param)
    {
        try
        {
            TTransaction::open('sample');
            $object = new Pessoa($param['id']);
            $object->delete();
            TTransaction::close();

            $this->onSearch($param);
            new TMessage('info', 'Registro excluído com sucesso');
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }
}


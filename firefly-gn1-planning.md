Planejamento de revisão do Firefly
==================================

> Data: 14/04/2020

## Primeira etapa

### Reproduzir categorias para centro de custo

*   Incluir o campo no cadastro de transação;
*   Incluir na seção "relatórios" o filtro por centro de custo;
*   Criar relatório de transações ao clicar sobre o centro de custo (show);

### Importação

*   Incluir o campo "centro de custo" no algoritmo de importação;

### Aparência

*   Incluir logomarca da GN1;
*   Alterar o título no template;
*   Retirar ajuda;

### Definições técnicas

*   Nome da nova tabela: `center_cost`

### Perguntas

*   O que a termo `journal`?
*   Quais locais o item `category` tem vínculo no model?    
*   Qual o local em que o Orçamento é vinculado a uma transação?
*   Onde o centro de custo poderá ser implementado no sistema?
*   Quais as dependências para serem criadas?
*   Como funciona o mecanismo de view/template?
*   Como utilizar o `artsan` para atualizar o banco de dados?

#### O que é o termo `journal`?

*   A exemplo do `Odoo`, um journal pode ser classificado como um `diário`. O Firefly III armazena cada transação financeira em "journals". Cada diário contém duas "transações". Um recebe dinheiro (-250 da sua conta bancária) e o outro o coloca em outra conta (+250 para a Amazon.com). https://docs.firefly-iii.org/en/latest/concepts/transactions.html

#### Quais locais o item `category` tem vínculo no model?

*   Tabelas:
    *   `categories`, `category_transaction` e `category_transaction_journal`.

#### Qual o local em que o Orçamento é vinculado a uma transação?

*   O orçamento é vinculado a uma transação-filha, pois ela pode ser dividida em cada item separadamente e não no valor total.

#### Onde o centro de custo poderá ser implementado no sistema?

*   Ele pode ser implementado semelhantemente a um orçamento, vinculado a uma transação-filha. 

#### Quais as dependências para serem criadas?

*   OK `/app/Models/`: definição dos campos;
*   OK `/database/`: utilizar o artisan para fazer a migração do banco de dados;
*   OK `/app/Services/Destroy`: Serviço que define ação de remoção;
*   OK `/app/Services/Update`: Serviço que define ação de atualização (campos salvos);
*   OK `/app/Factory`: Métodos de criação de objetos durante listagem;
*   OK `/app/Helpers/Collector/TransactionCollector.php`: Métodos helper para manipulação de coleções;
*   OK `/resources/lang/`: traduções de texto para centro de custo;
*   OK `/app/Repositories`: interface e repositório;
*   OK `/app/Providers`: Classe para atribuir instância para `$user`; 
*   OK `/app/Import/Mapper/`: popula um dicionário de dados pelo ID;
*   OK `/app/Http/Request/ReportFormRequest.php`: lista de categorias (para relatório listas em dropdown?);
*   OK `/app/Http/Request/CostCenterFormRequest.php`: Regra de validação dos campos;
*   OK `/app/Api/V1/Controllers/`: Controller, injetando o repositório;
*   OK `/resources/views/v1/cost-center`: Views das páginas dos recursos;
*   OK `/resources/views/v1/list/cost-center.twig`: lista de centros de custos;
*   OK `/routes/web.php`: Define as rotas para serem colocadas nas views;
*   OK `/app/Support/Http/Controllers/AugumentData.php`: Não está claro o uso;
*   OK `/app/Support/Http/Controllers/AutoCompleteCollector.php`: Autocomplete de campos;
*   OK `app/Api/V1/Controllers/Chart/CostCenterController.php`: API de gráficos;
*   OK `app/Api/V1/Controllers/CostCenterController.php`: API;
*   OK `app/Api/V1/Requests/CostCenterRequest.php`: mapeamento de campos;
*   OK `app/Http/Controllers/Chart/CostCenterReportController.php`: gráficos;
*   OK `app/Transformers/CostCenterTransformer.php`: mapeamento de campos;
*   OK `/app/Support/Http/Controllers/ChartGeneration.php`: Geração de gráfico; 
*   OK `/app/Support/Http/Controllers/PeriodOverview.php`: Relatório por período;
*   `/app/Support/Http/Controllers/RenderPartialViews.php`: renderiza views parciais;
*   `/app/Support/Import/Routine/File/MappedValuesValidator.php`: Cria objetos pelo Container para job de importação;
*   OK `/routes/breadcrumbs.php`: Caminho na parte superior;
*   `/app/Support/Search/Search.php`: Resultado de busca;
*   OK `/app/Transformers`: Transforma o resultado do banco em array, recupera os campos do contexto;
*   `/tests/`: Todos os testes de unidade resultantes das mudanças realizadas no código;
*   `/public/v1/js/ff/categories/*`: definições de funções categorias;
*   OK `/public/v1/js/ff/common/autocomplete.js`: funções para definir onde obter a lista de itens para autocomplete; 
*   PL `/public/v1/js/ff/transacions/single/common.js`: chama a função de autocomplete para inicializar o componente; 
*   OK `/app/Support/Import/Placeholder/ImportTransaction.php`: Mapeamento dos campos informados no `.csv`; 
*   OK `/app/Support/Binder/CostCenterList.php`: Binder para o mapeamento da lista; 

#### Como funciona o mecanismo de view/template?

/resources/views/V1 

#### Como utilizar o `artsan` para atualizar o banco de dados?

Exemplos a seguir:

```bash
php artisan make:migration create_users_table --create=users

php artisan make:migration add_votes_to_users_table --table=users
```

Mais na documentação em: https://laravel.com/docs/5.8/blade

#### O que é o tipo Carbon?

Uma extensão para Datetime, prove métodos para manipulação de datas

#### Como funciona o IoC?

Por meio da função `app(<<interface>>)`: 

```php
/** @var CategoryRepositoryInterface $repository */
$repository = app(CategoryRepositoryInterface::class);
```

A função está declarada no `helper.php` que chama o `Container->getInstance()->make()` para contruir uma nova instância. 

#### Onde está o `Helper` para elementos da view, como ExpandedForm?

A classe `ExpandedForm.php` fornecer métodos de helper para renderizar componentes, cujo HTML fica em `/resources/views/v1/form` .

### Inicializar o banco

```
php artisan migrate --seed
php artisan firefly:upgrade-database
php artisan firefly:verify
php artisan cache:clear
```

### Importação do MsMoney

#### Exceções

Antes de importar é preciso comentar as linhas de 202 a 208 do arquivo `app\Import\Storage\ImportArrayStorage.php` para não lançar exceção em linhas duplicadas.

#### Manipulação do arquivo .qif

Para este processo é utilizado o arquivo em `/database/import/Convert-QifToCsv.psm1`. A saída esperada é um arquivo `.csv`.

#### Importando dados

A bilioteca `artisan` é utilizada para a importação do arquivo `.csv`.

```bash
 php artisan firefly:create-import file.csv config.json --start --token=
```

O token deve ser obtido no perfil do usuário.

#### Configuração dos campos de importação

Para a importação utilizando o command-line, é necessário criar um arquivo de configuração responsável por mapear os campos que serão inseridos em determinadas colunas do banco de dados. Ex.:

```json
{
    "file-type": "csv",
    "date-format": "d\/m\/Y",
    "has-headers": false,
    "delimiter": "|",
    "apply-rules": false,
    "specifics": [],
    "import-account": 1,
    "column-count": 8,
    "column-roles": [
        "date-transaction",
        "amount",
        "_ignore",
        "opposing-name",
        "_ignore",
        "category-name",
        "center-cost-name",
        "description"
    ],
    "column-do-mapping": [
        false,
        false,
        false,
        false,
        false,
        false,
        false,
        false
    ]
}
```

O item `import-account` corresponde ao identificador no banco de dados para a conta que as transações serão vinculadas, ou seja, cada arquivo `.csv` deve ser obrigatoriamente de uma conta previamente cadastrada no sistema.

#### Arquivos do sistema utilizados na importação

*   `/app/Http/Controllers/Import/JobStatusController.php`
*   `/app/Support/Import/Routine/File/CSVProcessor.php`
*   `/app/Support/Import/Routine/File/MappingConverger.php`
*   `/app/Support/Import/Routine/File/ImportableConverter.php`
*   `/app/Repositories/Journal/JournalRepository.php`
*   `/app/Services/Internal/Update/JournalUpdateService.php`
*   `/app/Services/Internal/Update/TransactionUpdateService.php`
*   `/app/Services/Internal/Support/TransactionServiceTrait.php`
*   `/app/Factory/TransactionFactory.php`

## Segunda etapa

*   Criar o CRUD de centros de custos;
*   Trocar o design pelo template do Prosocio;
*   Desenvolver suporte para multiusuários;

## Atualização do projeto com um branch original

Identificou que a próxima versão `4.8` implementa novos métodos que excluíram o local onde é utilizada entidade "CostCenter". Nesse caso será necessária uma nova análise para ajuste das saídas desses métodos. 

## Imagens

### Construção (MAC)

```
docker container run --name firefly -d -v firefly_iii_export:/var/www/firefly-iii/storage/export -v firefly_iii_upload:/var/www/firefly-iii/storage/upload -p 8080:80 -e APP_ENV=ABORL-CCF -e FF_APP_KEY=asljhf32q98fvaer8gvau8ASDUIhkasd -e APP_KEY=asljhf32q98fvaer8gvau8ASDUIhkasd -e DB_PORT=5432 -e FF_DB_PORT=5432 -e DB_CONNECTION=pgsql -e FF_DB_CONNECTION=pgsql -e DB_HOST=docker.for.mac.localhost -e  FF_DB_HOST=docker.for.mac.localhost -e DB_DATABASE=firefly -e FF_DB_NAME=firefly -e DB_USERNAME=postgres -e FF_DB_USER=postgres -e DB_PASSWORD=israel1207 -e FF_DB_PASSWORD=israel1207 firefly:cc2
```


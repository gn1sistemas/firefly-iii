Ms-Money para Firefly
=====================

> Autor: Daniel Marcoto\
> Data: 20/02/2020

## Apresentação

O projeto de importação é composto de vários passos em programas diferentes.

## MS-Money

O primeiro passo é abrir o arquivo de dados no MS-Money. Caso seja arquivo de backup selecione a opção do MS-Money "Abrir/trabalhar com um arquivo existente do Money ou do Quiken". Em seguida selecione o caminho do arquivo.

Será preciso exportar cada conta em um arquivo QIF. Siga os passos a seguir:

1.  No menu Arquivo selecione a opção "Exportar";
2.  Mantenha a opção "QIF Livre";
3.  Selecione o nome do arquivo e local que será salvo;
4.  Mantenha a opção Regular;
5.  Selecione a conta que será exportada;

Persista todos os arquivos QIF em uma pasta. Ao término do processo constará na pasta todos os arquivos de cada conta com a extensão `.qif`.

O nome do arquivo deverá ser o número do ID conta. Mais informações sobre o ID da conta no projeto interno do cliente.

## QIF para CSV

Este passo consiste em transforma o formato do MS-Money no formato reconhecido pelo Firefly, o processo consiste em um script do PowerShell. O script está no arquivo `automate-qif-csv.ps1`.

Abra o arquivo e substitua o valor das variáveis de caminhos para indicar o caminho da pasta com os arquivos QIF, variável `$initialBasePath`. Também defina a variável `$qifPath` com o caminho.

Após execução do arquivo o resultado será que no caminho definido em `$initialBasePath` constarão duas subpastas, `csv` e `json`, na primeira os arquivos em formato CSV para ser importados, os arquivos JSON são para o Firefly interpretar as colunas.

## Importação no Firefly

Este passo será para incluir no banco de dados do Firefly.

O primeiro passo é configurar um banco de dados do zero e executar os comandos a seguir para preparação do banco:

```bash
php artisan migrate --seed
php artisan firefly:upgrade-database
php artisan firefly:verify
php artisan cache:clear
```

Em seguida execute o Firefly no navegador para a configuração inicial ocorrer. Defina o novo usuário.

Execute o script de criação das contas. Mais informações sobre este passo na pasta do cliente.

Finalmente defina o caminho da variável `$initialBasePath` do arquivo `automate-import.ps1` com a rais da pasta que contém os arquivos CSV e JSON. Este arquivo chama o processo de importação do Firefly para cada conta.

A execução dos script pode levar até 30 minutos. Pode ocorrer do processo ficar parado muito tempo em um arquivo, neste caso, interrompa e reinicie do arquivo seguint da lista.

## Exportar o banco local

O próximo passo é no gerenciador do banco de dados do Postgres abrir o banco e escolher a opção "Backup". O modo do backup deve ser `plain`. O arquivo resultado será no formato `.sql`.

## Servidor de aplicação

O último passo é implantar na aplicação executando no servidor de produção.

O primeiro passo é copiar o script do banco de dados `.sql` para o ambiente de produção.

Caso a aplicação esteja executando é preciso interromper pelo comando:

````bash
docker-compose down
````

Reinicie todo o banco de dados removando todos os volumes:

```bash
docker volume prune
```

É preciso ajustar o arquivo `docker-compose.yml` incluindo uma linha na seguinte hierarquia:

    services: 
        firefly_iii_db: 
            volumes: 

A seguir a linha para incluir, substitua o nome do arquivo `.sql` pelo nome do arquivo exportado no passo anterior:

```yml
- "./firefly.sql:/docker-entrypoint-initdb.d/firefly.sql"
```

Em seguida inicie novamente a aplicação:

````bash
docker-compose up -d
````
# Teste Jumba

Este projeto foi feito utilizando as seguintes tecnologias:

- VueJS 2
- NuxtJS
- Laravel 9
- Docker

## Executando o projeto
Siga os passos abaixo para executar o projeto:

1. Entre na pasta docker.
2. Execute o comando:

```sh
$ docker-compose up --build
```
Isso criará os containers.

3. Execute o comando:  

```sh
$ docker exec jumba-backend php artisan job:dispatch DownloadDataJob <data_inicio> <data_final>
```  
- data_inicio: representa a data de início (YYYY-MM-DD).
- data_final: representa a data final (YYYY-MM-DD).

Exemplo:

```sh
$ docker exec jumba-backend php artisan job:dispatch DownloadDataJob 2022-12-16 2023-02-03
```  

Este comando baixará os dados do site da B3 iniciando na data 16/12/2022 e finalizando na data 03/02/2023.

### Screenshot do projeto

![Screenshot 1](https://github.com/rickhc3/jumba-test/blob/master/docker/img/screenshot.png)

# AluraDownloader
Biblioteca em PHP para baixar suas videoaulas favoritas da plataforma de cursos Alura.

### Como Usar

Você precisa ter o PHP ^8.1 e o Composer ^2.4 instalados. Para utilizar esta biblioteca, recomendo:
- Incluia-la como dependência; __ou__
- Clonar este repositório.

#### 1. Usando como dependência

Adicione o pacote hoiast/alura-downloader utilizando o [Composer](https://getcomposer.org/):

```composer require hoiast/alura-downloader```

Importe a classe AluraDownloader:
```php
use Hoiast\AluraDownloader\AluraDownloader;

// Insira credenciais válidas
$username = "email@email.com";
$password = "password";
$coursesURLs =  [
    "https://cursos.alura.com.br/course/php-composer",
]

// . Instantiate Downloader
$downloader = new AluraDownloader($username, $password);

// . Download courses
$downloader->downloadCourses($coursesURLs);
```


#### 2. Clonando o repositório
- Instale as dependencias: ```composer install```
- Prepare o arquivo de configuração:
    - Copie o arquivo ```configs.json.example``` para ```configs.json```.
    - Substitua os campos de _username_ e _password_ com credenciais válidas. 
    - No campo _coursesURLs_, coloque os endereços (URLs) de cada curso que deseja fazer download.

- Rode o arquivo ```download-courses.php``` com ```PHP ^8.1.0```.

### Agradecimentos
Agradeço aos projetos pela inspiração e mapeamento dos _endpoints_ da API mobile e web da plataforma Alura:

- [Alura Downloader](https://github.com/SirSavio/alura-downloader)
- [Gengar](https://github.com/v4p0r/gengar)
- [Alura Video Scrapper](https://github.com/reinaldomoreira/alura-video-scrapper)

Agradeço também à própria Alura pela disponibilização de cursos de qualidade e por manter uma plataforma de cursos online estruturada e organizada.

### Observações

Para realizar os downloads, certifique-se que a conta referente às credenciais utilizadas foi "matriculada" nos cursos listados. Para isso, entre na plataforma Alura e clique em __Iniciar Curso__ para realizar a "matrícula". Sem isso, não é possível obter informações sobre o curso através dos _endpoints_ e, consequentemente, realizar o download do seu conteúdo.

Este código foi desenvolvido para fins educacionais e de aprendizado. O uso indevido deste código não é responsabilidade de seu criador e pode resultar em punições por parte da Alura segundo seus [Termos de Uso](https://www.alura.com.br/termos-de-uso).

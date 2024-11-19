<?php

//O sistema deve permitir que um usuário administrador tenha acesso a fazer exclusão de anúncios e até mesmo usuários. 
//O administrador poderá gerar relatórios de dados do sistema, utilizando filtros como período, número de locações, número de anúncios, local, etc.

session_start();

//cookies
if(isset($_SESSION['id']) && !isset($_COOKIE['usuario'])) {
    $cookie_nome = $_SESSION['nome'];
    $cookie_id = $_SESSION['id'];
    setcookie('usuario', $cookie_nome, time() + 1800, '/');
    setcookie('id', $cookie_id, time() + 1800, '/');
    $_COOKIE['usuario'] = $_SESSION['nome'];
    $_COOKIE['id'] = $_SESSION['id'];
} elseif (isset($_COOKIE['usuario']) && !isset($_SESSION['id'])) {
    $_SESSION['nome'] = $_COOKIE['usuario'];
    $_SESSION['id'] = $_COOKIE['id'];
}


include 'conexao.php';

//exclue o imovel
if (isset($_POST['excluir_imovel'])) {
    $excluir = $conexao->prepare("DELETE FROM imovel WHERE ID_imovel=?");
    $excluir->bind_param('i', $_POST['excluir_imovel']);
    $excluir->execute();
    $resultado = 'Imóvel deletado.';
}

//exclue o usuario
if (isset($_POST['excluir_usuario'])) {
    //exclui todos os imoveis dele
    $excluir = $conexao->prepare("DELETE imovel FROM imovel INNER JOIN proprietario ON imovel.ID_proprietario=proprietario.ID_proprietario INNER JOIN usuario ON proprietario.CPF=usuario.cpf WHERE usuario.id=?");
    $excluir->bind_param('i', $_POST['excluir_usuario']);
    $excluir->execute();

    //exclue ele dos proprietarios
    $excluir = $conexao->prepare("DELETE proprietario FROM proprietario INNER JOIN usuario ON proprietario.CPF=usuario.cpf WHERE usuario.id=?");
    $excluir->bind_param('i', $_POST['excluir_usuario']);
    $excluir->execute();

    //exclue ele
    $excluir = $conexao->prepare("DELETE FROM usuario WHERE id=?");
    $excluir->bind_param('i', $_POST['excluir_usuario']);
    $excluir->execute();
    $resultado = 'Usuário e todos os imóveis relacionados foram deletados.';
}



//bane o usuario
if (isset($_POST['banir'])) {
    $banir = $conexao->prepare("UPDATE usuario SET banido=true WHERE id=?");
    $banir->bind_param('i', $_POST['banir']);
    $banir->execute();
    $resultado = 'Usuário banido.';
}

//desbane o usuario
if (isset($_POST['desbanir'])) {
    $desbanir = $conexao->prepare("UPDATE usuario SET banido=false WHERE id=?");
    $desbanir->bind_param('i', $_POST['desbanir']);
    $desbanir->execute();
    $resultado = 'Usuário desbanido.';
}

//verifica se houve uma pesquisa e executa ela
if (isset($_POST['barra_pesquisa']) || isset($_POST['botao_pesquisa']) || isset($_POST['tabela']))  {
    $tabela = $_POST['tabela'];
    $coluna = $_POST['botao_pesquisa'];
    $pesquisa = $conexao->prepare("SELECT * FROM $tabela WHERE $coluna = ?");
    $pesquisa->bind_param('s', $_POST['barra_pesquisa']);
    $pesquisa->execute();
    $pesquisa = $pesquisa->get_result();
}


$usuario = $conexao->prepare("SELECT * FROM usuario;");
$usuario->execute();
$usuario = $usuario->get_result();

$imovel = $conexao->prepare("SELECT * FROM imovel;");
$imovel->execute();
$imovel = $imovel->get_result();

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link rel="shortcut icon" href="logoHostfy.png">
    <link rel="stylesheet" href="styles.css">
    <style> 
        .body_error{ 
            background-color:#FEF6EE;  
            color: #C56126;
            align-items: center; 
            justify-content: center; 
            }

        .logo_error {
            width: 120px; 
            position: fixed; 
            left: 50px; 
            top: 35px;} 
            
        .a_error {
            text-decoration: none; 
            color: #5b2c12
            }
        
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #FEF6EE;
            padding: 15px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        .anuncio {
            display: inline-block;
            border: black 2px solid;
            padding: 10px;
        }

        .perfil {
            display: inline-block;
            border: black 2px solid;
            padding: 10px;
        }
        </style>
</head>
<body>

    <!-- caso seja o admin -->
    <div id='permitido' style="display: none;">
        <header>
            <!-- Botão do ícone de menu -->
            <button class="menu-icon" id="menu-toggle">
                <i class='bx bx-menu'></i>
            </button>

            <img src="logoHostfy.png" alt="logo" class="logo" />

            <!-- Campo de pesquisa -->
            <form method="post" action="pesquisar.php" class="search-form">
                <input type="text" name="pesquisar" class="search-input">
                <span>
                    <button type="submit" class="search-button">
                        <i class='bx bx-search'></i>
                    </button>
                </span>
            </form>
            <a href="logout.php" class="menu__link">Sair</a>
        </header>  
        <p><?php if(isset($resultado)) { echo $resultado;} ?></p>
        <div id="opções">
            <button onclick="usuarios()">Usuários</button>
            <button onclick="imoveis()">Anúncios</button>
            <button onclick="pesquisa()">Pesquisa</button>
        </div>

        <div id="usuarios" style="display: none;">
            <button onclick="voltar()">voltar</button>  
            <br> 

            <?php foreach ($usuario as $usuarios) { ?>
                <?php 
                    //impede de mostrar o admin
                    if ($usuarios['id'] == 0) {

                    } else {?>
                        <div class="perfil">
                            <p>Nome: <?php echo $usuarios['nome'];?></p>
                            <p>CPF: <?php echo $usuarios['cpf'];?></p>
                            <p>Email: <?php echo $usuarios['email'];?></p>
                            <p>Telefone: <?php echo $usuarios['telefone'];?></p>
                            <p>Endereço: <?php echo $usuarios['endereco'];?></p>
                            <p>Cidade: <?php echo $usuarios['cidade'];?></p>
                            <p>UF: <?php echo $usuarios['estado'];?></p>
                            <p>Banido: 
                                <?php
                                    if ($usuarios['banido'] == 0) {
                                        echo 'Não';
                                    } elseif ($usuarios['banido'] == 1) {
                                        echo 'Sim';
                                    }
                                ?>
                            </p>
                            <form action="admin.php" method="post">
                                <?php if ($usuarios['banido'] == 1) {?>
                                    <button value="<?php echo $usuarios['id']; ?>" name="desbanir">Desbanir</button>
                                <?php } elseif ($usuarios['banido'] == 0) { ?>
                                    <button name="banir" value="<?php echo $usuarios['id']; ?>">Banir</button>
                                <?php } ?>
                                <button value="<?php echo $usuarios['id'];?>" name="excluir_usuario">Excluir</button>
                            </form>
                        </div>
                        <?php 
                            //pula uma linha, controla quantos imoveis aparecem lado a lado
                            if (!isset($i)) {
                                $u = 0;
                            }
                            $u++;
                            //apenas alterar a comparação para aumentar ou diminuir
                            if ($u == 2) {
                                echo '<br>';
                                $u = 0;
                            }
                        ?>
                    <?php } ?>
            <?php } ?>
        </div>

        <div id="imoveis" style="display: none;">
            <button onclick="voltar()">voltar</button>
            <br>
            
            <?php foreach ($imovel as $imoveis) { ?>
                <div class="anuncio">  
                    <?php 
                        //cpf do proprietario
                        $proprietario = $conexao->prepare("SELECT cpf FROM proprietario WHERE ID_proprietario=?");
                        $proprietario->bind_param('i', $imoveis['ID_proprietario']);
                        $proprietario->execute();
                        $proprietario = $proprietario->get_result();
                        $proprietario = $proprietario->fetch_assoc();
                    ?>
                    <p>Proprietário: <?php echo $proprietario['cpf'];?></p>
                    <p>CEP: <?php echo $imoveis['CEP'];?></p>
                    <p>Nome do Imóvel<?php echo $imoveis['Nome_imovel'];?></p>
                    <p>N° de pessoas: <?php echo $imoveis['Numero_pessoas'];?></p>
                    <p>Rua: <?php echo $imoveis['Rua'];?></p>
                    <p>N°: <?php echo $imoveis['Numero'];?></p>
                    <p>Bairro: <?php echo $imoveis['Bairro'];?></p>
                    <p>Cidade: <?php echo $imoveis['Cidade'];?></p>
                    <p>Estado: <?php echo $imoveis['UF'];?></p>
                    <p>Categoria: 
                        <?php 
                            if ($imoveis['id_categoria'] == 1) {
                                echo 'Casa';
                            } elseif ($imoveis['id_categoria'] == 2) {
                                echo 'Apartamento';
                            } elseif ($imoveis['id_categoria'] == 3) {
                                echo 'Sítio';
                            }
                        ?>
                    </p>
                    <p>Características: 
                        <?php 
                          if ($imoveis['id_checklist'] == 1) {
                            echo 'Garagem ';
                          }
                          if ($imoveis['id_checklist'] == 2) {
                            echo 'Bicicleta ';
                          }
                          if ($imoveis['id_checklist'] == 3) {
                            echo 'Pet Friendly ';
                          }
                          if ($imoveis['id_checklist'] == 4) {
                            echo 'Churrasqueira ';
                          }
                          if ($imoveis['id_checklist'] == 5) {
                            echo 'Piscina ';
                          }
                          if ($imoveis['id_checklist'] == 6) {
                            echo 'Sauna ';
                          }
                          if ($imoveis['id_checklist'] == 7) {
                            echo 'Quadra Poliesportiva';
                          }
                        ?>    
                    </p>
                    <p><?php echo $imoveis['Descrição'];?></p>
                    <?php
                        //separa as imagens em um array
                        $imgs = explode(", ", $imoveis['imagens']);
                    ?>
                    <img src="<?php echo $imgs[0]; ?>" style="height: 250px; Width: 250px;">
                    <form action="admin.php" method="post">
                        <button value="<?php echo $imoveis['ID_imovel'];?>" name="excluir_imovel">Excluir</button>
                    </form>
                </div>
                <?php 
                    //pula uma linha, controla quantos imoveis aparecem lado a lado
                    if (!isset($i)) {
                        $i = 0;
                    }
                    $i++;
                    //apenas alterar a comparação para aumentar ou diminuir
                    if ($i == 2) {
                        echo '<br>';
                        $i = 0;
                    }
                ?>
            <?php } ?>
        </div>

        <div id="pesquisa" style="display: none;">
            <button onclick="voltar()">voltar</button>
            <form action="admin.php" method="post">
                <input list="" type="text" id="barra_pesquisa" name="barra_pesquisa" required >
                <button style="display: none;" type="submit" name="botao_pesquisa" id="botao_pesquisa">Enviar</button>
                <input id="tabela" name="tabela" style="display: none;">
            </form>
            
            <div id="tipo">
                <button onclick="p_usuario()">Usuário</button>
                <button onclick="p_imovel()">Imóvel</button>
            </div>

            <div id="listas_usuario" style="display: none;">
                <button onclick="nome_u()">Nome</button>
                <button onclick="cpf_u()">CPF</button>
                <button onclick="endereco_u()">Endereço</button>
                <button onclick="cidade_u()">Cidade</button>
                <button onclick="uf_u()">Estado</button>
                <button onclick="telefone_u()">Telefone</button>
                <button onclick="email_u()">Email</button>
            </div>

            <div id="listas_imovel" style="display: none;">
                <button onclick="cep_i()">CEP</button>
                <button onclick="nome_i()">Nome</button>
                <button onclick="rua_i()">Rua</button>
                <button onclick="numero_i()">Numero</button>
                <button onclick="bairro_i()">Bairro</button>
                <button onclick="cidade_i()">Cidade</button>
                <button onclick="uf_i()">Estado</button>
            </div>

            <div id="pesquisa_usuario" style="display: none;">
                <?php foreach ($pesquisa as $usuarios) { ?>
                    <?php 
                        //impede de mostrar o admin
                        if ($usuarios['id'] == 0) {

                        } else {?>
                            <div class="perfil">
                                <p>Nome: <?php echo $usuarios['nome'];?></p>
                                <p>CPF: <?php echo $usuarios['cpf'];?></p>
                                <p>Email: <?php echo $usuarios['email'];?></p>
                                <p>Telefone: <?php echo $usuarios['telefone'];?></p>
                                <p>Endereço: <?php echo $usuarios['endereco'];?></p>
                                <p>Cidade: <?php echo $usuarios['cidade'];?></p>
                                <p>UF: <?php echo $usuarios['estado'];?></p>
                                <p>Banido: 
                                    <?php
                                        if ($usuarios['banido'] == 0) {
                                            echo 'Não';
                                        } elseif ($usuarios['banido'] == 1) {
                                            echo 'Sim';
                                        }
                                    ?>
                                </p>
                                <form action="admin.php" method="post">
                                    <?php if ($usuarios['banido'] == 1) {?>
                                        <button value="<?php echo $usuarios['id']; ?>" name="desbanir">Desbanir</button>
                                    <?php } elseif ($usuarios['banido'] == 0) { ?>
                                        <button name="banir" value="<?php echo $usuarios['id']; ?>">Banir</button>
                                    <?php } ?>
                                    <button value="<?php echo $usuarios['id'];?>" name="excluir_usuario">Excluir</button>
                                </form>
                            </div>
                            <?php 
                                //pula uma linha, controla quantos imoveis aparecem lado a lado
                                if (!isset($i)) {
                                    $u = 0;
                                }
                                $u++;
                                //apenas alterar a comparação para aumentar ou diminuir
                                if ($u == 2) {
                                    echo '<br>';
                                    $u = 0;
                                }
                            ?>
                        <?php } ?>
                <?php } ?>
            </div>

            <div id="pesquisa_imovel" style="display: none;">
                <?php foreach ($pesquisa as $imoveis) { ?>
                    <div class="anuncio">  
                        <?php 
                            //cpf do proprietario
                            $proprietario = $conexao->prepare("SELECT cpf FROM proprietario WHERE ID_proprietario=?");
                            $proprietario->bind_param('i', $imoveis['ID_proprietario']);
                            $proprietario->execute();
                            $proprietario = $proprietario->get_result();
                            $proprietario = $proprietario->fetch_assoc();
                        ?>
                        <p>Proprietário: <?php echo $proprietario['cpf'];?></p>
                        <p>CEP: <?php echo $imoveis['CEP'];?></p>
                        <p>Nome do Imóvel<?php echo $imoveis['Nome_imovel'];?></p>
                        <p>N° de pessoas: <?php echo $imoveis['Numero_pessoas'];?></p>
                        <p>Rua: <?php echo $imoveis['Rua'];?></p>
                        <p>N°: <?php echo $imoveis['Numero'];?></p>
                        <p>Bairro: <?php echo $imoveis['Bairro'];?></p>
                        <p>Cidade: <?php echo $imoveis['Cidade'];?></p>
                        <p>Estado: <?php echo $imoveis['UF'];?></p>
                        <p>Categoria: 
                            <?php 
                                if ($imoveis['id_categoria'] == 1) {
                                    echo 'Casa';
                                } elseif ($imoveis['id_categoria'] == 2) {
                                    echo 'Apartamento';
                                } elseif ($imoveis['id_categoria'] == 3) {
                                    echo 'Sítio';
                                }
                            ?>
                        </p>
                        <p>Características: 
                            <?php 
                            if ($imoveis['id_checklist'] == 1) {
                                echo 'Garagem ';
                            }
                            if ($imoveis['id_checklist'] == 2) {
                                echo 'Bicicleta ';
                            }
                            if ($imoveis['id_checklist'] == 3) {
                                echo 'Pet Friendly ';
                            }
                            if ($imoveis['id_checklist'] == 4) {
                                echo 'Churrasqueira ';
                            }
                            if ($imoveis['id_checklist'] == 5) {
                                echo 'Piscina ';
                            }
                            if ($imoveis['id_checklist'] == 6) {
                                echo 'Sauna ';
                            }
                            if ($imoveis['id_checklist'] == 7) {
                                echo 'Quadra Poliesportiva';
                            }
                            ?>    
                        </p>
                        <p><?php echo $imoveis['Descrição'];?></p>
                        <?php
                            //separa as imagens em um array
                            $imgs = explode(", ", $imoveis['imagens']);
                        ?>
                        <img src="<?php echo $imgs[0]; ?>" style="height: 250px; Width: 250px;">
                        <form action="admin.php" method="post">
                            <button value="<?php echo $imoveis['ID_imovel'];?>" name="excluir_imovel">Excluir</button>
                        </form>
                    </div>
                    <?php 
                        //pula uma linha, controla quantos imoveis aparecem lado a lado
                        if (!isset($i)) {
                            $i = 0;
                        }
                        $i++;
                        //apenas alterar a comparação para aumentar ou diminuir
                        if ($i == 2) {
                            echo '<br>';
                            $i = 0;
                        }
                    ?>
                <?php } ?>
            </div>
        </div>

    </div>

     <!-- caso não seja o admin e acessem a pagina -->
    <div id='negado' style="display: none;">
    <br>
        <div class="body_error" style=" background-color: #fff3f3; border: 1px solid orange; padding: 15px; border-radius: 8px; text-align: center; "> 
            <img src="logoHostfy.png" alt="logo" class="logo_error">
            <h1>Você não tem permissão para acessar essa página!</h1>
            <p>Volte ao <a class="a_error" href="index.php">início.</a></p>
        </div>   
    </div>

    <!-- listas da pesquisa -->
    

        <datalist id="nome_u">
            <?php
                $lista_u = $conexao->prepare("SELECT DISTINCT nome FROM usuario WHERE id!=0;");
                $lista_u->execute();
                $lista_u = $lista_u->get_result();
                foreach ($lista_u as $info) {
            ?>
            <option><?php echo $info['nome']; ?></option>
            <?php } ?>
        </datalist>

        <datalist id="cpf_u">
            <?php
                $lista_u = $conexao->prepare("SELECT DISTINCT cpf FROM usuario WHERE id!=0;");
                $lista_u->execute();
                $lista_u = $lista_u->get_result();
                foreach ($lista_u as $info) {
            ?>
            <option><?php echo $info['cpf']; ?></option>
            <?php } ?>
        </datalist>

        <datalist id="endereco_u">
            <?php
                $lista_u = $conexao->prepare("SELECT DISTINCT endereco FROM usuario WHERE id!=0;");
                $lista_u->execute();
                $lista_u = $lista_u->get_result();
                foreach ($lista_u as $info) {
            ?>
            <option><?php echo $info['endereco']; ?></option>
            <?php } ?>
        </datalist>

        <datalist id="cidade_u">
            <?php
                $lista_u = $conexao->prepare("SELECT DISTINCT cidade FROM usuario WHERE id!=0;");
                $lista_u->execute();
                $lista_u = $lista_u->get_result();
                foreach ($lista_u as $info) {
            ?>
            <option><?php echo $info['cidade']; ?></option>
            <?php } ?>
        </datalist>

        <datalist id="uf_u">
            <?php
                $lista_u = $conexao->prepare("SELECT DISTINCT estado FROM usuario WHERE id!=0;");
                $lista_u->execute();
                $lista_u = $lista_u->get_result();
                foreach ($lista_u as $info) {
            ?>
            <option><?php echo $info['estado']; ?></option>
            <?php } ?>
        </datalist>

        <datalist id="telefone_u">
            <?php
                $lista_u = $conexao->prepare("SELECT DISTINCT telefone FROM usuario WHERE id!=0;");
                $lista_u->execute();
                $lista_u = $lista_u->get_result();
                foreach ($lista_u as $info) {
            ?>
            <option><?php echo $info['telefone']; ?></option>
            <?php } ?>
        </datalist>

        <datalist id="email_u">
            <?php
                $lista_u = $conexao->prepare("SELECT DISTINCT email FROM usuario WHERE id!=0;");
                $lista_u->execute();
                $lista_u = $lista_u->get_result();
                foreach ($lista_u as $info) {
            ?>
            <option><?php echo $info['email']; ?></option>
            <?php } ?>
        </datalist>

        <datalist id="cep_i">
            <?php
                $lista_i = $conexao->prepare("SELECT DISTINCT CEP FROM imovel;");
                $lista_i->execute();
                $lista_i = $lista_i->get_result();
                foreach ($lista_i as $info) {
            ?>
            <option><?php echo $info['CEP']; ?></option>
            <?php } ?>
        </datalist>

        <datalist id="nome_i">
            <?php
                $lista_i = $conexao->prepare("SELECT DISTINCT Nome_imovel FROM imovel;");
                $lista_i->execute();
                $lista_i = $lista_i->get_result();
                foreach ($lista_i as $info) {
            ?>
            <option><?php echo $info['Nome_imovel']; ?></option>
            <?php } ?>
        </datalist>

        <datalist id="rua_i">
            <?php
                $lista_i = $conexao->prepare("SELECT DISTINCT Rua FROM imovel;");
                $lista_i->execute();
                $lista_i = $lista_i->get_result();
                foreach ($lista_i as $info) {
            ?>
            <option><?php echo $info['Rua']; ?></option>
            <?php } ?>
        </datalist>

        <datalist id="numero_i">
            <?php
                $lista_i = $conexao->prepare("SELECT DISTINCT Numero  FROM imovel;");
                $lista_i->execute();
                $lista_i = $lista_i->get_result();
                foreach ($lista_i as $info) {
            ?>
            <option><?php echo $info['Numero']; ?></option>
            <?php } ?>
        </datalist>

        <datalist id="bairro_i">
            <?php
                $lista_i = $conexao->prepare("SELECT DISTINCT Bairro FROM imovel;");
                $lista_i->execute();
                $lista_i = $lista_i->get_result();
                foreach ($lista_i as $info) {
            ?>
            <option><?php echo $info['Bairro']; ?></option>
            <?php } ?>
        </datalist>

        <datalist id="cidade_i">
            <?php
                $lista_i = $conexao->prepare("SELECT DISTINCT Cidade FROM imovel;");
                $lista_i->execute();
                $lista_i = $lista_i->get_result();
                foreach ($lista_i as $info) {
            ?>
            <option><?php echo $info['Cidade']; ?></option>
            <?php } ?>
        </datalist>

        <datalist id="uf_i">
            <?php
                $lista_i = $conexao->prepare("SELECT DISTINCT UF FROM imovel;");
                $lista_i->execute();
                $lista_i = $lista_i->get_result();
                foreach ($lista_i as $info) {
            ?>
            <option><?php echo $info['UF']; ?></option>
            <?php } ?>
        </datalist>

    <script>

        function permitido() {
            document.getElementById('permitido').style.display='';
            document.getElementById('negado').style.display='none';
        }

        function negado() {
            document.getElementById('permitido').style.display='none';
            document.getElementById('negado').style.display='';
        }

        function usuarios() {
            document.getElementById('opções').style.display='none'
            document.getElementById('usuarios').style.display=''
        }

        function imoveis() {
            document.getElementById('opções').style.display='none'
            document.getElementById('imoveis').style.display=''
        }

        function voltar() {
            document.getElementById('opções').style.display=''
            document.getElementById('usuarios').style.display='none'
            document.getElementById('imoveis').style.display='none'
            document.getElementById('pesquisa').style.display='none'
        }

        function pesquisa() {
            document.getElementById('opções').style.display='none'
            document.getElementById('pesquisa').style.display=''
        }

        function p_usuario() {
            document.getElementById('listas_usuario').style.display=''
            document.getElementById('listas_imovel').style.display='none'
            document.getElementById('tabela').value='usuario'
        }

        function p_imovel() {
            document.getElementById('listas_usuario').style.display='none'
            document.getElementById('listas_imovel').style.display=''
            document.getElementById('tabela').value='imovel'
        }

        function nome_u() {
            const pesquisa = document.getElementById('barra_pesquisa');
            pesquisa.setAttribute('list', 'nome_u');
            document.getElementById('botao_pesquisa').value='nome'
            document.getElementById('botao_pesquisa').style.display=''
        }

        function cpf_u() {
            const pesquisa = document.getElementById('barra_pesquisa');
            pesquisa.setAttribute('list', 'cpf_u');
            document.getElementById('botao_pesquisa').value='cpf'
            document.getElementById('botao_pesquisa').style.display=''
        }

        function endereco_u() {
            const pesquisa = document.getElementById('barra_pesquisa');
            pesquisa.setAttribute('list', 'endereco_u');
            document.getElementById('botao_pesquisa').value='endereco'
            document.getElementById('botao_pesquisa').style.display=''
        }
        function cidade_u() {
            const pesquisa = document.getElementById('barra_pesquisa');
            pesquisa.setAttribute('list', 'cidade_u');
            document.getElementById('botao_pesquisa').value='cidade'
            document.getElementById('botao_pesquisa').style.display=''
        }
        function uf_u() {
            const pesquisa = document.getElementById('barra_pesquisa');
            pesquisa.setAttribute('list', 'uf_u');
            document.getElementById('botao_pesquisa').value='estado'
            document.getElementById('botao_pesquisa').style.display=''
        }
        function telefone_u() {
            const pesquisa = document.getElementById('barra_pesquisa');
            pesquisa.setAttribute('list', 'telefone_u');
            document.getElementById('botao_pesquisa').value='telefone'
            document.getElementById('botao_pesquisa').style.display=''
        }
        function email_u() {
            const pesquisa = document.getElementById('barra_pesquisa');
            pesquisa.setAttribute('list', 'email_u');
            document.getElementById('botao_pesquisa').value='email'
            document.getElementById('botao_pesquisa').style.display=''
        }
        function cep_i() {
            const pesquisa = document.getElementById('barra_pesquisa');
            pesquisa.setAttribute('list', 'cep_i');
            document.getElementById('botao_pesquisa').value='CEP'
            document.getElementById('botao_pesquisa').style.display=''
        }
        function nome_i() {
            const pesquisa = document.getElementById('barra_pesquisa');
            pesquisa.setAttribute('list', 'nome_i');
            document.getElementById('botao_pesquisa').value='Nome_imovel'
            document.getElementById('botao_pesquisa').style.display=''
        }
        function rua_i() {
            const pesquisa = document.getElementById('barra_pesquisa');
            pesquisa.setAttribute('list', 'rua_i');
            document.getElementById('botao_pesquisa').value='Rua'
            document.getElementById('botao_pesquisa').style.display=''
        }
        function numero_i() {
            const pesquisa = document.getElementById('barra_pesquisa');
            pesquisa.setAttribute('list', 'numero_i');
            document.getElementById('botao_pesquisa').value='Numero'
            document.getElementById('botao_pesquisa').style.display=''
        }
        function bairro_i() {
            const pesquisa = document.getElementById('barra_pesquisa');
            pesquisa.setAttribute('list', 'bairro_i');
            document.getElementById('botao_pesquisa').value='Bairro'
            document.getElementById('botao_pesquisa').style.display=''
        }
        function cidade_i() {
            const pesquisa = document.getElementById('barra_pesquisa');
            pesquisa.setAttribute('list', 'cidade_i');
            document.getElementById('botao_pesquisa').value='Cidade'
            document.getElementById('botao_pesquisa').style.display=''
        }
        function uf_i() {
            const pesquisa = document.getElementById('barra_pesquisa');
            pesquisa.setAttribute('list', 'uf_i');
            document.getElementById('botao_pesquisa').value='UF'
            document.getElementById('botao_pesquisa').style.display=''
        }

        function pesquisa_feita(){
            document.getElementById('pesquisa').style.display=''
            document.getElementById('opções').style.display='none'
            document.getElementById('imoveis').style.display='none'
            document.getElementById('usuarios').style.display='none'
        }
        function pesquisa_usuario() {
            document.getElementById('pesquisa_usuario').style.display=''
            document.getElementById('pesquisa_imovel').style.display='none'
        }
        function pesquisa_imovel() {
            document.getElementById('pesquisa_usuario').style.display='none'
            document.getElementById('pesquisa_imovel').style.display=''
        }

    </script>

    <?php

        //verificar a permissão
        if (isset($_SESSION['id']) && $_SESSION['id'] == 0) {
            echo '<script>permitido()</script>';
        } else {
            echo '<script>negado()</script>';
        }

        if (isset($_POST['barra_pesquisa']) || isset($_POST['botao_pesquisa']) || isset($_POST['tabela'])) {
            echo '<script>pesquisa_feita()</script>';
            if ($tabela == 'usuario') {
                echo '<script>pesquisa_usuario()</script>';
            } else {
                echo '<script>pesquisa_imovel()</script>';
            }
        }
    ?>

    <?php mysqli_close($conexao);?>

</body>
</html>
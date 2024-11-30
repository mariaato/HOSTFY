<?php

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
    $excluir = $conexao->prepare("DELETE FROM imovel_checklist WHERE id_imovel=?");
    $excluir->bind_param('i', $_POST['excluir_imovel']);
    $excluir->execute();

    $excluir = $conexao->prepare("DELETE FROM locação WHERE id_imovel=?");
    $excluir->bind_param('i', $_POST['excluir_imovel']);
    $excluir->execute();
    
    $excluir = $conexao->prepare("DELETE FROM imovel WHERE ID_imovel=?");
    $excluir->bind_param('i', $_POST['excluir_imovel']);
    $excluir->execute();
    $resultado = 'Imóvel deletado.';
}

//exclue o usuario
if (isset($_POST['excluir_usuario'])) {
    $imoveis_checklists = $conexao->prepare("SELECT * FROM imovel WHERE ID_proprietario=?");
    $imoveis_checklists->bind_param('i', $_POST['excluir_usuario']);
    $imoveis_checklists->execute();
    $imoveis_c_resultado = $imoveis_checklists->get_result();
    foreach ($imoveis_c_resultado as $i) {
        $excluir = $conexao->prepare("DELETE imovel_checklist FROM imovel_checklist WHERE id_imovel=?");
        $excluir->bind_param('i', $i['ID_imovel']);
        $excluir->execute();

        $excluir = $conexao->prepare("DELETE FROM locação WHERE id_imovel=?");
        $excluir->bind_param('i', $i['ID_imovel']);
        $excluir->execute();
    }

    //exclui todos os imoveis dele
    $excluir = $conexao->prepare("DELETE imovel FROM imovel INNER JOIN usuario ON imovel.id_proprietario=usuario.id WHERE usuario.id=?");
    $excluir->bind_param('i', $_POST['excluir_usuario']);
    $excluir->execute();

    $excluir = $conexao->prepare("DELETE FROM locador WHERE id_locador=?");
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
    $processo = 'pesquisa';
}

//verifica por um relatorio
if (isset($_POST['data_rel'])) {
    $data = explode('-', $_POST['data_rel']);
    $rel = $conexao->prepare("SELECT * FROM locação WHERE MONTH(Data_inicial) = ? AND YEAR(Data_inicial) = ?");
    $rel->bind_param('ss', $data[1], $data[0]);
    $rel->execute();
    $rel = $rel->get_result();
    $processo = 'relatorio';
}

$usuario = $conexao->prepare("SELECT * FROM usuario;");
$usuario->execute();
$usuario = $usuario->get_result();

$imovel = $conexao->prepare("SELECT * FROM imovel;");
$imovel->execute();
$imovel = $imovel->get_result();

$checklist = $conexao->prepare("SELECT * FROM checklist;");
$checklist->execute();
$checklist = $checklist->get_result();

$categoria = $conexao->prepare("SELECT * FROM categoria;");
$categoria->execute();
$categoria = $categoria->get_result();

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link rel="shortcut icon" href="logoHostfy.png">
    <link rel="stylesheet" href="styles.css?">
    <link rel="stylesheet" href="https://unpkg.com/boxicons@latest/css/boxicons.min.css">

    <style> 
     #main-content {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
            width: 100%;
            background-color: #FEF6EE;
            margin-bottom: 60px;
        }
        
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
            font-size: 20px;
            display: flex;
            justify-content:center;
            align-items: center;
            background-color: #FEF6EE;
            padding: 15px;
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

        .relatorio {
            display: inline-block;
            border: black 2px solid;
            padding: 10px;
        } 
        .submit-btn {
            background-color: #D97C41 ;
            color: white;
            border: none;
            padding: 8px;
            border-radius: 12px;
            font-size: 20px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin: 10px;
        }

        .submit-btn:hover {
            background-color: #c96f36;
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

        <a href="admin.php">
            <img src="logoHostfy.png" alt="logo" class="logo" />
        </a>
        <a href="logout.php" class="menu__link">Sair</a>

</header>  

<div class="sidebar" id="sidebar">

            <a href='admin.php'>Área Admin</a>
            <a href="index.php">Área inicial </a>
            <a href="perfilhtml.php">Perfil</a>
            <a href="imoveis.php">Cadastre seu imóvel</a>
            <a href="meus_imoveis.php">Imóveis Cadastrados</a>
            <a href="quemsomos.php">Quem Somos</a>
            <a href="duvidas.php">Dúvidas</a>
                
    </div>

    <!-- Overlay para quando o menu estiver aberto -->
    <div class="overlay" id="overlay"></div>

<div class="main-content" id="main-content">

        <p><?php if(isset($resultado)) { echo $resultado;} ?></p>
        <div id="opções">
            <button onclick="usuarios()" class="submit-btn">Usuários</button>
            <button onclick="imoveis()" class="submit-btn">Anúncios</button>
            <button onclick="pesquisa()" class="submit-btn">Pesquisa</button>
            <button onclick="relatorio()" class="submit-btn">Relatório</button>
        </div>

        <div id="usuarios" style="display: none;">
            <button onclick="voltar()" class="submit-btn">voltar</button>  
            <br> 
            <p>Número de usuarios: <?php echo mysqli_num_rows($usuario)-1 ?></p>
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
                                    <button value="<?php echo $usuarios['id']; ?>" name="desbanir" class="submit-btn">Desbanir</button>
                                <?php } elseif ($usuarios['banido'] == 0) { ?>
                                    <button name="banir" value="<?php echo $usuarios['id']; ?>" class="submit-btn">Banir</button>
                                <?php } ?>
                                <button value="<?php echo $usuarios['id'];?>" name="excluir_usuario" class="submit-btn">Excluir</button>
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
            <button onclick="voltar()" class="submit-btn">voltar</button>
            <br>
            <p>Número de imóveis: <?php echo mysqli_num_rows($imovel) ?></p>
            <?php foreach ($imovel as $imoveis) { ?>
                <div class="anuncio">  
                    <?php 
                        //cpf do proprietario
                        $proprietario = $conexao->prepare("SELECT cpf FROM usuario WHERE id=?");
                        $proprietario->bind_param('i', $imoveis['ID_proprietario']);
                        $proprietario->execute();
                        $proprietario = $proprietario->get_result();
                        $proprietario = $proprietario->fetch_assoc();

                        $pesquisa_checklist = $conexao->prepare("SELECT * FROM checklist INNER JOIN imovel_checklist ON checklist.id_checklist=imovel_checklist.id_checklist WHERE imovel_checklist.id_imovel=?");
                        $pesquisa_checklist->bind_param('i', $imoveis['ID_imovel']);
                        $pesquisa_checklist->execute();
                        $resultado_checklist = $pesquisa_checklist->get_result();
                        $lista_checklist = [];
                        $ids = [];
                        while ($linha = $resultado_checklist->fetch_assoc()) {
                            $lista_checklist[] = $linha['nome_checklist'];
                            $ids[] = $linha['id_checklist']; 
                        }
                        $imovel_checklist = implode(', ', $lista_checklist);
                    ?>
                    <p>Proprietário: <?php echo $proprietario['cpf'];?></p>
                    <p>CEP: <?php echo $imoveis['CEP'];?></p>
                    <p>Nome do Imóvel: <?php echo $imoveis['Nome_imovel'];?></p>
                    <p>N° de pessoas: <?php echo $imoveis['Numero_pessoas'];?></p>
                    <p>Rua: <?php echo $imoveis['Rua'];?></p>
                    <p>N°: <?php echo $imoveis['Numero'];?></p>
                    <p>Bairro: <?php echo $imoveis['Bairro'];?></p>
                    <p>Cidade: <?php echo $imoveis['Cidade'];?></p>
                    <p>Estado: <?php echo $imoveis['UF'];?></p>
                    <p>Categoria: 
                        <?php 
                            foreach ($categoria as $c) {
                                if ($c['id_categoria'] == $imoveis['id_categoria']) {
                                    echo $c['nome_categoria'];
                                }
                            }
                        ?>
                    </p>
                    <p>Características: 
                        <?php 
                            echo $imovel_checklist . '.';
                        ?>    
                    </p>
                    <p>Descrição: <?php echo $imoveis['Descrição'];?></p>
                    <?php
                        //separa as imagens em um array
                        $imgs = explode(", ", $imoveis['imagens']);
                    ?>
                    <img src="<?php echo $imgs[0]; ?>" style="height: 250px; Width: 250px;">
                    <form action="admin.php" method="post">
                        <button value="<?php echo $imoveis['ID_imovel'];?>" name="excluir_imovel" class="submit-btn">Excluir</button>
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

        <div id="pesquisa" class="pesquisa" style="display: none;" >
            <button onclick="voltar()" class="submit-btn">voltar</button>
            <div id="tipo">
                <button onclick="p_usuario()" class="">Usuário</button>
                <button onclick="p_imovel()" class="">Imóvel</button>
            </div>
            <form action="admin.php" method="post">
                <input list="" type="text" id="barra_pesquisa" name="barra_pesquisa" required >
                <button style="display: none;" type="submit" name="botao_pesquisa" id="botao_pesquisa" class="submit-btn">Enviar</button>
                <input id="tabela" name="tabela" style="display: none;">
            </form>
            
           

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
                        $proprietario = $conexao->prepare("SELECT cpf FROM usuario WHERE id=?");
                        $proprietario->bind_param('i', $imoveis['ID_proprietario']);
                        $proprietario->execute();
                        $proprietario = $proprietario->get_result();
                        $proprietario = $proprietario->fetch_assoc();

                        $pesquisa_checklist = $conexao->prepare("SELECT * FROM checklist INNER JOIN imovel_checklist ON checklist.id_checklist=imovel_checklist.id_checklist WHERE imovel_checklist.id_imovel=?");
                        $pesquisa_checklist->bind_param('i', $imoveis['ID_imovel']);
                        $pesquisa_checklist->execute();
                        $resultado_checklist = $pesquisa_checklist->get_result();
                        $lista_checklist = [];
                        $ids = [];
                        while ($linha = $resultado_checklist->fetch_assoc()) {
                            $lista_checklist[] = $linha['nome_checklist'];
                            $ids[] = $linha['id_checklist']; 
                        }
                        $imovel_checklist = implode(', ', $lista_checklist);
                    ?>
                    <p>Proprietário: <?php echo $proprietario['cpf'];?></p>
                    <p>CEP: <?php echo $imoveis['CEP'];?></p>
                    <p>Nome do Imóvel: <?php echo $imoveis['Nome_imovel'];?></p>
                    <p>N° de pessoas: <?php echo $imoveis['Numero_pessoas'];?></p>
                    <p>Rua: <?php echo $imoveis['Rua'];?></p>
                    <p>N°: <?php echo $imoveis['Numero'];?></p>
                    <p>Bairro: <?php echo $imoveis['Bairro'];?></p>
                    <p>Cidade: <?php echo $imoveis['Cidade'];?></p>
                    <p>Estado: <?php echo $imoveis['UF'];?></p>
                    <p>Categoria: 
                        <?php 
                            foreach ($categoria as $c) {
                                if ($c['id_categoria'] == $imoveis['id_categoria']) {
                                    echo $c['nome_categoria'];
                                }
                            }
                        ?>
                    </p>
                    <p>Características: 
                        <?php 
                            echo $imovel_checklist . '.';
                        ?>    
                    </p>
                    <p>Descrição: <?php echo $imoveis['Descrição'];?></p>
                    <?php
                        //separa as imagens em um array
                        $imgs = explode(", ", $imoveis['imagens']);
                    ?>
                    <img src="<?php echo $imgs[0]; ?>" style="height: 250px; Width: 250px;">
                    <form action="admin.php" method="post">
                        <button value="<?php echo $imoveis['ID_imovel'];?>" name="excluir_imovel" class="submit-btn">Excluir</button>
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

        <div id="relatorio" style="display: none;">
            <button onclick="voltar()" class="submit-btn">voltar</button>
            <br>
            <?php
                $NL = $conexao->prepare("SELECT * FROM locador;");
                $NL->execute();
                $NL = $NL->get_result();
                $NP = $conexao->prepare("SELECT DISTINCT id FROM usuario INNER JOIN imovel WHERE usuario.id=imovel.ID_proprietario");
                $NP->execute();
                $NP = $NP->get_result();
            ?>
            <div class="relatorio">
                <p>Número de Usuarios: <?php echo mysqli_num_rows($usuario) ?></p>
                <p>Número de Imóveis: <?php echo mysqli_num_rows($imovel) ?></p>
                <p>Número de Proprietários: <?php echo mysqli_num_rows($NP) ?></p>
                <p>Número de Locadores: <?php echo mysqli_num_rows($NL) ?></p>
                <form action="admin.php" method="post">
                    <input type="month" id="data_rel" name="data_rel" required>
                    <br>
                    <button type="submit" class="submit-btn">Gerar relatorio desse período</button>
                </form>
                <div id="rel_periodo" style="display: none;">
                    <p>Imóveis alugados no mês <?php if (isset($data[1])) {echo $data[1];} ?> de <?php if(isset($data[0])) {echo $data[0];}?>:</p>
                    <?php if(isset($rel)) { echo mysqli_num_rows($rel);} ?>
                </div>
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
        function relatorio() {
            document.getElementById('opções').style.display='none'
            document.getElementById('relatorio').style.display=''
        }
        function voltar() {
            document.getElementById('opções').style.display=''
            document.getElementById('usuarios').style.display='none'
            document.getElementById('imoveis').style.display='none'
            document.getElementById('pesquisa').style.display='none'
            document.getElementById('relatorio').style.display='none'
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
        function mostrar_rel() {
            document.getElementById('rel_periodo').style.display=''
        } 

    </script>

    <?php

        //verificar a permissão
        if (isset($_SESSION['id']) && $_SESSION['id'] == 0) {
            echo '<script>permitido()</script>';
        } else {
            echo '<script>negado()</script>';
        }

        if (isset($processo) && $processo == 'pesquisa') {
            echo '<script>pesquisa_feita()</script>';
            $processo = '';
            if ($tabela == 'usuario') {
                echo '<script>pesquisa_usuario()</script>';
            } else {
                echo '<script>pesquisa_imovel()</script>';
            }
        } elseif (isset($processo) && $processo == 'relatorio') {
            echo '<script>relatorio()</script>';
            echo '<script>mostrar_rel()</script>';
        }
    ?>

    <?php mysqli_close($conexao);?>
</div>
</body>
<script>
        // Função para alternar o menu lateral
        const menuToggle = document.getElementById('menu-toggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('main-content');
        const overlay = document.getElementById('overlay');

        // Função de alternância para abrir/fechar o menu e o overlay
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('sidebar-active');
            mainContent.classList.toggle('content-shift');
            overlay.classList.toggle('overlay-active');
        });

        // Função para fechar o menu se clicar fora (no overlay)
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('sidebar-active');
            mainContent.classList.remove('content-shift');
            overlay.classList.remove('overlay-active');
        });
    </script>
</html>
<?php
// function gerarAnuncio($id, $imagem, $titulo, $avaliacao, $tags) {
//     // Início do HTML do anúncio
//     $html = '<div class="anuncio-card">';
    
//     $html .= '<a href="reserva.php?id=' . $id . '" class="anuncio-link">';

//     // Adiciona a imagem do imóvel
//     //explode separa a string de imagens em um array
//     $imagem = explode(", ", $imagem);
//     //apenas a primeira está sendo enviada para o anuncio, se quiser utilizar das outras apenas alterar o indice de $imagem[?]
//     $html .= '<img src="' . $imagem[0] . '" alt="' . $titulo . '" class="anuncio-imagem">';
    
//     // Adiciona o título do imóvel
//     $html .= '<h3 class="anuncio-titulo">' . $titulo . '</h3>';
    
//     // Adiciona a avaliação com ícone de estrela
//     $html .= '<div class="anuncio-avaliacao">';
//     $html .= 'R$ ' . number_format($avaliacao, 2);
//     $html .= '</div>';
    
//     // Adiciona as tags de características
//     if (!empty($tags)) {
//         $html .= '<div class="anuncio-tags">';
//         foreach ($tags as $tag) {
//             $html .= '<span class="tag">' . $tag . '</span>';
//         }
//         $html .= '</div>';
//     }
    
//     $html .= '</a>';
    
//     // Fechamento do HTML do anúncio
//     $html .= '</div>';
    
//     // Retorna o HTML gerado
//     return $html;
// }

function gerarAnuncio($id, $imagem, $titulo, $avaliacao, $tags) {
    // Início do HTML do anúncio
    $html = '<div class="anuncio-card">';

    $html .= '<a href="reserva.php?id=' . $id . '" class="anuncio-link">';
    
    // Adiciona o carrossel de imagens
    $imagens = explode(", ", $imagem);
    $html .= '<div id="carousel-' . $id . '" class="carousel slide anuncio-imagem" data-bs-ride="carousel">';
    
    // Indicadores (pontos abaixo do carrossel)
    $html .= '<div class="carousel-indicators">';
    foreach ($imagens as $index => $img) {
        $activeClass = $index === 0 ? 'active' : '';
        $html .= '<button type="button" data-bs-target="#carousel-' . $id . '" data-bs-slide-to="' . $index . '" class="' . $activeClass . '" aria-current="' . ($activeClass ? 'true' : 'false') . '" aria-label="Slide ' . ($index + 1) . '"></button>';
    }
    $html .= '</div>';
    
    // Slides das imagens
    $html .= '<div class="carousel-inner">';
    foreach ($imagens as $index => $img) {
        $activeClass = $index === 0 ? 'active' : '';
        $html .= '<div class="carousel-item ' . $activeClass . '">';
        $html .= '<img src="' . $img . '" class="anuncio-imagem" alt="' . $titulo . '">';
        $html .= '</div>';
    }
    $html .= '</div>';
    
    // Controles do carrossel
    $html .= '<button class="carousel-control-prev" type="button" data-bs-target="#carousel-' . $id . '" data-bs-slide="prev">';
    $html .= '<span class="carousel-control-prev-icon" aria-hidden="true"></span>';
    $html .= '<span class="visually-hidden">Previous</span>';
    $html .= '</button>';
    $html .= '<button class="carousel-control-next" type="button" data-bs-target="#carousel-' . $id . '" data-bs-slide="next">';
    $html .= '<span class="carousel-control-next-icon" aria-hidden="true"></span>';
    $html .= '<span class="visually-hidden">Next</span>';
    $html .= '</button>';
    
    $html .= '</div>'; // Fim do carrossel
    
    // Adiciona o título do imóvel
    $html .= '<h3 class="anuncio-titulo">' . $titulo . '</h3>';
    
    // Adiciona a avaliação com ícone de estrela
    $html .= '<div class="anuncio-avaliacao">';
    $html .= 'R$ ' . number_format($avaliacao, 2);
    $html .= '</div>';
    
    // Adiciona as tags de características
    if (!empty($tags)) {
        $html .= '<div class="anuncio-tags">';
        foreach ($tags as $tag) {
            $html .= '<span class="tag">' . $tag . '</span>';
        }
        $html .= '</div>';
    }
    
    $html .= '</a>';
    
    // Fechamento do HTML do anúncio
    $html .= '</div>';
    
    // Retorna o HTML gerado
    return $html;
}
?>

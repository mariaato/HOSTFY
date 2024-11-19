<?php
function gerarAnuncio($imagem, $titulo, $avaliacao, $tags) {
    // Início do HTML do anúncio
    $html = '<div class="anuncio-card">';
    
    // Adiciona a imagem do imóvel
    $html .= '<img src="' . $imagem . '" alt="' . $titulo . '" class="anuncio-imagem">';
    
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
    
    // Fechamento do HTML do anúncio
    $html .= '</div>';
    
    // Retorna o HTML gerado
    return $html;
}

?>

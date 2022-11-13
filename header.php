<!DOCTYPE html>
<html lang="en">
<head>
     <meta charset="<?php bloginfo("charset") ?>">
     <meta http-equiv="X-UA-Compatible" content="IE=edge">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">

     <link rel="stylesheet" href="/assets/css/normalize.css">
     <link rel="stylesheet" href="/assets/css/default.css">
     <link rel="stylesheet" href="/assets/css/layout.css">
     <link rel="stylesheet" href="/assets/css/media-queries.css">

     <?php wp_head(); ?>
</head>
<body>
     <header class="wrapper wl-header">
          <div class="container flex-justify-between">
               <div class="wl-logo">
                    <a href=""><?php the_custom_logo(); ?></a>
               </div>
               <div class="wl-header-other">
                    <p><?php echo get_theme_mod( 'title_text_block' ); ?></p>
               </div>
          </div>
     </header>
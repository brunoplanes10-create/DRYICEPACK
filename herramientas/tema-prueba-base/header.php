<?php
/* Cabecera de PRUEBA que imita la de dryicepack.es (solo para ver páginas en local). */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
<style>
  body{margin:0;font-family:"Open Sans",Arial,sans-serif;font-size:14px;color:#666;background:#fff}
  .dip-topbar{background:#1F3C55;color:#fff;font-size:13px;font-weight:600}
  .dip-topbar .in{max-width:1200px;margin:0 auto;padding:10px 32px;display:flex;gap:18px;justify-content:center;flex-wrap:wrap}
  .dip-header{background:#fff;border-bottom:1px solid #e1edf6;position:sticky;top:0;z-index:40}
  .dip-header-wrap{max-width:1200px;margin:0 auto;padding:12px 32px;display:flex;align-items:center;gap:20px}
  .dip-logo img{width:198px;height:auto;display:block}
  .dip-right{margin-left:auto;display:flex;align-items:center;gap:22px}
  .dip-nav{display:flex;gap:22px}
  .dip-link{color:#1b2f42;text-decoration:none;font-weight:650;font-size:15.5px}
  .dip-pills{display:flex;gap:10px}
  .nav-pill{border-radius:999px;padding:11px 18px;font-weight:800;font-size:14.5px;text-decoration:none;border:2px solid #1F3C55}
  .nav-pill--filled{background:#1F3C55;color:#fff}.nav-pill--outline{background:#fff;color:#1F3C55}
  .dip-burger{display:none;font-size:26px;color:#1F3C55}
  @media (max-width:980px){.dip-nav,.dip-pills{display:none}.dip-burger{display:block}.dip-header-wrap{padding:12px 16px}.dip-logo img{width:150px}}
  .dip-footer{background:#0F2433;color:#b7d2e6;font-size:14px}.dip-footer .in{max-width:1200px;margin:0 auto;padding:40px 32px}
  /* === Enlace "Halloween" del menú (esto es lo que se añade en la web real) === */
  .dip-link--halloween{display:inline-flex;align-items:center;gap:7px;color:#c2410c !important}
  .dip-link--halloween::before{content:"";width:7px;height:7px;border-radius:50%;background:#ff6b1a;animation:dipHwLatido 1.8s ease-out infinite}
  @keyframes dipHwLatido{0%{opacity:1;transform:scale(1)}70%{opacity:.3;transform:scale(1.8)}100%{opacity:1;transform:scale(1)}}
  @media (prefers-reduced-motion:reduce){.dip-link--halloween::before{animation:none}}
</style>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="dip-topbar"><div class="in"><span>info@dryicepack.es</span><span>936 73 76 41</span><span>Envío 24h</span><span>L–V · 9:00–18:00</span></div></div>
<header class="dip-header">
  <div class="dip-header-wrap">
    <a class="dip-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url( get_stylesheet_directory_uri() ); ?>/logo-dryicepack.webp" alt="DRYICEPACK" width="220" height="44"></a>
    <div class="dip-right">
      <nav class="dip-nav" aria-label="Navegación principal">
        <a class="dip-link" href="/que-es-el-hielo-seco/">¿Qué es?</a>
        <a class="dip-link" href="/aplicaciones-del-hielo-seco/">Aplicaciones</a>
        <a class="dip-link dip-link--halloween" href="/hielo-seco-halloween/">Halloween</a>
        <a class="dip-link" href="/contacto/">Contacto</a>
      </nav>
      <div class="dip-pills">
        <a class="nav-pill nav-pill--filled" href="/producto/hielo-seco/">Comprar hielo seco</a>
        <a class="nav-pill nav-pill--outline" href="/programar-suministro-de-hielo-seco/">Programar suministro</a>
      </div>
      <span class="dip-burger" aria-hidden="true">☰</span>
    </div>
  </div>
</header>

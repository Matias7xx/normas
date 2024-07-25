function abrirPagina(URL,w, h) {
    // w = 1000;
    // h = 500;
    LeftPosition = (screen.width) ? (screen.width-w)/2 : 0;
    TopPosition = (screen.height) ? (screen.height-h)/2 : 0;
    window.open(URL, 'janela', 'width='+w+', height='+h+',top='+TopPosition+',left='+LeftPosition+',scrollbars=no, status=no, toolbar=no, location=no, menubar=no, resizable=no, fullscreen=no')
  }

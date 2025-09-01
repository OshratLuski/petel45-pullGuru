YUI().use('node', function (Y) {
  if (Y.one('div.menubar .dropdown-menu[role="menu"]')) {
    let i = 0;
    Y.all('div.menubar .dropdown-menu[role="menu"] .dropdown-item').each(function(){
      if (i > 14) this.remove();
      i++;
    });
  }
});
 
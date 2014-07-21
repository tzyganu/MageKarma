function toggleMenu(buttonId, menuId)
{
    var menu = document.getElementById(menuId),
        image = document.getElementById(buttonId).getElementsByTagName('IMG')[0],
        isOpened = window.getComputedStyle(menu, null).getPropertyValue('display') == 'block';

    image.setAttribute('src', image.getAttribute('src').replace(/[^\/]+$/, isOpened ? 'open.png' : 'close.png'));
    menu.style.display = isOpened ? 'none' : 'block';
}

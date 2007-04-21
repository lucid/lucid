/****************************\
|        Psych Desktop       |
|      Wallpaper Engine      |
|   (c) 2006 Psych Designs   |
\***************************/

function loadWallpaperPrefs()
{
api.registry.getValue(0, "bgimg", "setWallpaper");
api.registry.getValue(0, "bgcolor", "setWallpaperColor");
}

function setWallpaper(image)
{
if(image)
{
document.getElementById("wallpaper").innerHTML="<img width='100%' height='100%' src='"+image+"'>";
}
else
{
document.getElementById("wallpaper").innerHTML="&nbsp";
}
}

function setWallpaperColor(color)
{
if( document.documentElement && document.documentElement.style ) {
    document.documentElement.style.backgroundColor = color; }
if( document.body && document.body.style ) {
    document.body.style.backgroundColor = color; }
    document.bgColor = color;
}
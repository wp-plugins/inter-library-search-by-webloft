function ilsfbShare (url, winWidth, winHeight)
{
    var winTop = (screen.height / 2) - (winHeight / 2);
    var winLeft = (screen.width / 2) - (winWidth / 2);
    window.open('http://www.facebook.com/sharer.php?u=' + url, 'sharer', 'top=' + winTop + ',left=' + winLeft + ',toolbar=0,status=0,width=' + winWidth + ',height=' + winHeight);
}

function hidereglitreLoading ()
{
    document.getElementById('divreglitreLoading').style.display = "none";
    document.getElementById('divreglitreFrameHolder').style.display = "block";
}

function showreglitreLoading ()
{
    document.getElementById('divreglitreLoading').style.display = "block";
    document.getElementById('divreglitreFrameHolder').style.display = "none";
}

function hidereglitreframeLoading ()
{
    //document.getElementById('divreglitreframeLoading').style.display = "none";
    document.getElementById('divreglitreframeFrameHolder').style.opacity = "1";
	document.html.scrollTop = -10000;
}

function showreglitreframeLoading ()
{
    //document.getElementById('divreglitreframeLoading').style.display = "block";
    document.getElementById('divreglitreframeFrameHolder').style.opacity = "0.2";
}

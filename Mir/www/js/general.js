
// Toggle the left menu on small screens
if(document.getElementById("btn-toggle-left-menu"))
{
    document.getElementById("btn-toggle-left-menu").addEventListener("click", function(event)
    {
        if(document.getElementById("left-menu").style.visibility == "hidden")
        {
            document.getElementById("left-menu").style.visibility = "visible";
        }
        else
        {
            document.getElementById("left-menu").style.visibility = "hidden";
        }
    });
}
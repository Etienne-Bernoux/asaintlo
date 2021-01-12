/**
 * @package     ResponsiveScrollingTables
 *
 * @copyright   Copyright (C) 2014 T J Dixon Limited. All rights reserved. http://www.tjdixon.com/
 * @license     Licensed under the GNU GPLv3 http://www.gnu.org/licenses/gpl.html or later
 */

function responsiveTables()
{
    var tables = document.querySelectorAll("table");
    for (var i = 0; i < tables.length; i++)
	{
        if (tables[i].scrollWidth > tables[i].parentNode.clientWidth && (tables[i].parentNode.tagName.toLowerCase() != "div" || tables[i].parentNode.getAttribute("data-responsive") != "res-div"))
		{
            var d = document.createElement("div");
			var parent = tables[i].parentNode;
            d.appendChild(tables[i].cloneNode(true));
            d.setAttribute("style","overflow-x:scroll;");
			d.setAttribute("data-responsive","res-div");
            parent.replaceChild(d,tables[i]);
        }
		else if (tables[i].scrollWidth <= tables[i].parentNode.clientWidth && (tables[i].parentNode.tagName.toLowerCase() == "div" && tables[i].parentNode.getAttribute("data-responsive") == "res-div"))
		{
			var tablesParent = tables[i].parentNode;
			var parentsParent = tablesParent.parentNode;
			parentsParent.replaceChild(tables[i].cloneNode(true), tablesParent);
		}
    }
}

window.addEventListener('resize', function (e) { responsiveTables(); });

document.addEventListener("DOMContentLoaded", function () { responsiveTables(); });

<!--

function swap_cbtn(img, action)
{
	switch (action)
	{
		case 'out': if (img.src == __ADMIN_URL__ + 'images/window_close_down.gif') { img.src = __ADMIN_URL__ + 'images/window_close.gif'; } break;
		case 'down': img.src = __ADMIN_URL__ + 'images/window_close_down.gif'; break;
		case 'up':
			if (img.src == __ADMIN_URL__ + 'images/window_close_down.gif')
			{
				img.src = __ADMIN_URL__ + 'images/window_close.gif';
				eval('hide' + img.parentNode.parentNode.id+'();');
			}
			break;
		default: break;
	}
}


function AddProduct(id, idTC, q)
{
	document.ManagerProductsFlagship.todo.value = 'add_' + id;
	document.ManagerProductsFlagship.submit();
}

function DelProduct(id)
{
	document.ManagerProductsFlagship.todo.value = 'del_' + id;
	document.ManagerProductsFlagship.submit();
}

function ClearProducts()
{
	document.ManagerProductsFlagship.todo.value = 'clear';
	document.ManagerProductsFlagship.submit();
}

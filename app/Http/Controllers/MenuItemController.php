<?php
namespace App\Http\Controllers;

use App\Http\Requests\MenuItemRequest;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Repositories\MenuItemRepository;

class MenuItemController extends Controller
{
	protected $menuItemRepository;

	public function __construct(MenuItemRepository $menuItemRepository)
	{
		$this->menuItemRepository = $menuItemRepository;
	}

	public function create(Menu $menu)
	{
		$menu->load("children");

		return view("pages.menuitems.form", compact("menu"));
	}

	public function store(MenuItemRequest $request)
	{
        $menuItem = $this->menuItemRepository->create($request->validated());

        $this->menuItemRepository->syncGroups($request, $menuItem);

        return redirect()->route("menus.edit", [$menuItem->menu_uuid]);
	}

	public function show(MenuItem $menuitem)
	{
		//
	}

    public function edit(Menu $menu, MenuItem $menuitem)
    {
        return view("pages.menuitems.form", compact("menu", "menuitem"));
    }

	public function update(MenuItemRequest $request, Menu $menu, MenuItem $menuitem)
	{
        $this->menuItemRepository->update($menuitem, $request->validated());

        $this->menuItemRepository->syncGroups($request, $menuitem);

        return redirect()->route("menus.edit", [$menuitem->menu_uuid]);
	}

	public function destroy(MenuItem $menuitem)
	{
		return redirect()->route("menuitems.index");
	}
}

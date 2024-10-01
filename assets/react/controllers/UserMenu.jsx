import {Menu, MenuButton, MenuItem, MenuItems} from "@headlessui/react";
import {useNavigate} from "react-router-dom";

export default function UserMenu() {
    const navigate = useNavigate();

    function handleLogout() {
        sessionStorage.removeItem('access_token');

        navigate("/");
    }

    return (
        <Menu as="div" className="relative ml-3">
            <div>
                <MenuButton className="relative flex rounded-full bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800">
                    <span className="absolute -inset-1.5" />
                    <span className="sr-only">Open user menu</span>
                    <img
                        alt=""
                        src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"
                        className="h-8 w-8 rounded-full"
                    />
                </MenuButton>
            </div>
            <MenuItems
                transition
                className="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 transition focus:outline-none data-[closed]:scale-95 data-[closed]:transform data-[closed]:opacity-0 data-[enter]:duration-100 data-[leave]:duration-75 data-[enter]:ease-out data-[leave]:ease-in"
            >
                <MenuItem>
                    <a href="#" className="block px-4 py-2 text-sm text-gray-700 data-[focus]:bg-gray-100">
                        Your Profile
                    </a>
                </MenuItem>
                <MenuItem>
                    <a href="#" className="block px-4 py-2 text-sm text-gray-700 data-[focus]:bg-gray-100">
                        Settings
                    </a>
                </MenuItem>
                <MenuItem>
                    <a
                        href="/api/security/logout"
                        className="block px-4 py-2 text-sm text-gray-700 data-[focus]:bg-gray-100"
                        onClick={handleLogout}
                    >
                        Sign out
                    </a>
                </MenuItem>
            </MenuItems>
        </Menu>
    );
}

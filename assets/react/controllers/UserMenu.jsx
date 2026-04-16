import { Menu, MenuButton, MenuItem, MenuItems } from "@headlessui/react";
import { useNavigate, Link } from "react-router-dom";
import { useDispatch, useSelector } from "react-redux";
import { logout } from "../../redux/reducers/authSlice";
import { UserCircleIcon, Cog6ToothIcon, ArrowLeftOnRectangleIcon, ChevronDownIcon } from "@heroicons/react/24/outline";

export default function UserMenu() {
    const navigate = useNavigate();
    const dispatch = useDispatch();
    const { user } = useSelector((state) => state.authentication);

    function handleLogout() {
        dispatch(logout())
        navigate("/");
    }

    return (
        <Menu as="div" className="relative ml-3">
            <div>
                <MenuButton className="group relative flex items-center space-x-2 rounded-full bg-white dark:bg-gray-800 p-1 pr-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-100 dark:border-gray-700">
                    <span className="sr-only">Open user menu</span>
                    <img
                        alt=""
                        src={user?.picture || "https://images.unsplash.com/vector-1738312097380-45562da00459?q=80&w=1760&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D"}
                        className="h-8 w-8 rounded-full object-cover ring-2 ring-white"
                    />
                    <div className="hidden md:flex flex-col items-start leading-tight max-w-[120px]">
                        <span className="text-xs font-semibold text-gray-900 dark:text-gray-100 truncate w-full text-left">{user?.email || 'User'}</span>
                        <span className="text-[10px] text-gray-500 dark:text-gray-400">Account</span>
                    </div>
                    <ChevronDownIcon className="h-4 w-4 text-gray-400 group-hover:text-gray-600 transition-colors" />
                </MenuButton>
            </div>
            <MenuItems
                transition
                className="absolute right-0 z-10 mt-2 w-56 origin-top-right rounded-2xl bg-white dark:bg-gray-800 p-1.5 shadow-xl ring-1 ring-black ring-opacity-5 dark:ring-gray-700 transition focus:outline-none data-[closed]:scale-95 data-[closed]:transform data-[closed]:opacity-0 data-[enter]:duration-100 data-[leave]:duration-75 data-[enter]:ease-out data-[leave]:ease-in"
            >
                <div className="px-3 py-2 border-b border-gray-50 dark:border-gray-700 mb-1">
                    <p className="text-xs font-medium text-gray-400 dark:text-gray-500 uppercase tracking-wider">Settings</p>
                </div>
                <MenuItem>
                    <Link to="#" className="group flex items-center rounded-xl px-3 py-2 text-sm text-gray-700 dark:text-gray-200 transition-colors data-[focus]:bg-indigo-50 dark:data-[focus]:bg-indigo-900/20 data-[focus]:text-indigo-700 dark:data-[focus]:text-indigo-400">
                        <UserCircleIcon className="mr-3 h-5 w-5 text-gray-400 dark:text-gray-500 group-data-[focus]:text-indigo-600 dark:group-data-[focus]:text-indigo-400" />
                        Your Profile
                    </Link>
                </MenuItem>
                <MenuItem>
                    <Link to="#" className="group flex items-center rounded-xl px-3 py-2 text-sm text-gray-700 dark:text-gray-200 transition-colors data-[focus]:bg-indigo-50 dark:data-[focus]:bg-indigo-900/20 data-[focus]:text-indigo-700 dark:data-[focus]:text-indigo-400">
                        <Cog6ToothIcon className="mr-3 h-5 w-5 text-gray-400 dark:text-gray-500 group-data-[focus]:text-indigo-600 dark:group-data-[focus]:text-indigo-400" />
                        Settings
                    </Link>
                </MenuItem>
                <div className="my-1 border-t border-gray-50 dark:border-gray-700" />
                <MenuItem>
                    <button
                        className="group flex w-full items-center rounded-xl px-3 py-2 text-sm text-red-600 dark:text-red-400 transition-colors data-[focus]:bg-red-50 dark:data-[focus]:bg-red-900/20"
                        onClick={handleLogout}
                    >
                        <ArrowLeftOnRectangleIcon className="mr-3 h-5 w-5 text-red-400 dark:text-red-500 group-data-[focus]:text-red-600 dark:group-data-[focus]:text-red-400" />
                        Sign out
                    </button>
                </MenuItem>
            </MenuItems>
        </Menu>
    );
}

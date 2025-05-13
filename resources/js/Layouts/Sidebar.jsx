import React from 'react';
import { Link } from '@inertiajs/react';
import { 
    HomeIcon, 
    UserGroupIcon, 
    ClipboardDocumentListIcon,
    ChartBarIcon,
    CalendarIcon,
    CogIcon
} from '@heroicons/react/24/outline';

const navigation = [
    {
        name: 'Dashboard',
        href: route('dashboard'),
        icon: HomeIcon,
        current: route().current('dashboard')
    },
    {
        name: 'Proyectos',
        href: route('projects.index'),
        icon: ClipboardDocumentListIcon,
        current: route().current('projects.*')
    },
    {
        name: 'Clientes',
        href: route('customers.index'),
        icon: UserGroupIcon,
        current: route().current('customers.*')
    },
    {
        name: 'Predicción de Entregas',
        href: route('prediccion.index'),
        icon: ChartBarIcon,
        current: route().current('prediccion.index')
    },
    {
        name: 'Calendario',
        href: route('calendar'),
        icon: CalendarIcon,
        current: route().current('calendar')
    },
    {
        name: 'Configuración',
        href: route('settings'),
        icon: CogIcon,
        current: route().current('settings')
    }
];

const Sidebar = () => {
    return (
        <div className="flex flex-col h-full bg-gray-800 text-white">
            <div className="flex-1 flex flex-col pt-5 pb-4 overflow-y-auto">
                <div className="flex items-center flex-shrink-0 px-4">
                    <h1 className="text-xl font-bold">Sistema Gestión 2</h1>
                </div>
                <nav className="mt-5 flex-1 px-2 space-y-1">
                    {navigation.map((item) => (
                        <Link
                            key={item.name}
                            href={item.href}
                            className={`group flex items-center px-2 py-2 text-sm font-medium rounded-md ${
                                item.current
                                    ? 'bg-gray-900 text-white'
                                    : 'text-gray-300 hover:bg-gray-700 hover:text-white'
                            }`}
                        >
                            <item.icon
                                className={`mr-3 flex-shrink-0 h-6 w-6 ${
                                    item.current ? 'text-white' : 'text-gray-400 group-hover:text-gray-300'
                                }`}
                                aria-hidden="true"
                            />
                            {item.name}
                        </Link>
                    ))}
                </nav>
            </div>
        </div>
    );
};

export default Sidebar; 

import { Sidebar, SidebarContent, SidebarGroup, SidebarGroupContent, 
  SidebarGroupLabel, SidebarMenu, SidebarMenuItem, SidebarMenuButton } from "@/components/ui/sidebar";
import { Database, Key, Settings, Users } from "lucide-react";
import { Outlet } from "react-router-dom";

export const AdminLayout = () => {
  const menuItems = [
    { title: "Settings", icon: Settings, href: "/admin/settings" },
    { title: "API Keys", icon: Key, href: "/admin/api-keys" },
    { title: "Usage Stats", icon: Database, href: "/admin/stats" },
    { title: "Users", icon: Users, href: "/admin/users" },
  ];

  return (
    <div className="min-h-screen flex w-full">
      <Sidebar>
        <SidebarContent>
          <SidebarGroup>
            <SidebarGroupLabel>Admin Panel</SidebarGroupLabel>
            <SidebarGroupContent>
              <SidebarMenu>
                {menuItems.map((item) => (
                  <SidebarMenuItem key={item.title}>
                    <SidebarMenuButton asChild>
                      <a href={item.href}>
                        <item.icon className="w-5 h-5" />
                        <span>{item.title}</span>
                      </a>
                    </SidebarMenuButton>
                  </SidebarMenuItem>
                ))}
              </SidebarMenu>
            </SidebarGroupContent>
          </SidebarGroup>
        </SidebarContent>
      </Sidebar>
      <main className="flex-1 p-6">
        <Outlet />
      </main>
    </div>
  );
};

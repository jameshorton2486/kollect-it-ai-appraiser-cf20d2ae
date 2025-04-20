import { BrowserRouter, Routes, Route } from "react-router-dom";
import { SidebarProvider } from "@/components/ui/sidebar";
import { AdminLayout } from "@/components/admin/AdminLayout";
import { ApiKeys } from "@/components/admin/ApiKeys";
import { UsageStats } from "@/components/admin/UsageStats";
import { MainLayout } from "@/layouts/MainLayout";
import { AppraiserDashboard } from "@/components/AppraiserDashboard";

function App() {
  return (
    <BrowserRouter>
      <SidebarProvider>
        <Routes>
          <Route path="/" element={<MainLayout />}>
            <Route index element={<AppraiserDashboard />} />
            <Route path="/admin" element={<AdminLayout />}>
              <Route path="settings" element={<ApiKeys />} />
              <Route path="api-keys" element={<ApiKeys />} />
              <Route path="stats" element={<UsageStats />} />
            </Route>
          </Route>
        </Routes>
      </SidebarProvider>
    </BrowserRouter>
  );
}

export default App;


import { Outlet } from "react-router-dom";
import { AppHeader } from "@/components/AppHeader";

export const MainLayout = () => {
  return (
    <div className="min-h-screen bg-background">
      <AppHeader />
      <main className="container mx-auto py-6">
        <Outlet />
      </main>
    </div>
  );
};

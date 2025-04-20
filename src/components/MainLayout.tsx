
import { AppHeader } from "./AppHeader";
import { Footer } from "./Footer";
import { ThemeToggle } from "./ThemeToggle";
import { Card } from "./ui/card";
import { HelpCircle } from "lucide-react";

interface MainLayoutProps {
  children: React.ReactNode;
}

export const MainLayout = ({ children }: MainLayoutProps) => {
  return (
    <div className="flex min-h-screen flex-col">
      <div className="fixed top-4 right-4 z-50 flex items-center gap-2">
        <Card className="p-2 hidden sm:flex items-center gap-2 bg-secondary/10">
          <HelpCircle className="h-4 w-4 text-secondary" />
          <span className="text-sm text-secondary">Need help? Contact support</span>
        </Card>
        <ThemeToggle />
      </div>
      <AppHeader />
      <main className="flex-1 container mx-auto px-4 py-6 sm:px-6 lg:px-8">
        {children}
      </main>
      <Footer />
    </div>
  );
};

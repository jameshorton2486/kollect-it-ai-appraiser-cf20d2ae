
import { AppHeader } from "./AppHeader";
import { Footer } from "./Footer";
import { ThemeToggle } from "./ThemeToggle";

interface MainLayoutProps {
  children: React.ReactNode;
}

export const MainLayout = ({ children }: MainLayoutProps) => {
  return (
    <div className="flex min-h-screen flex-col">
      <div className="fixed top-4 right-4 z-50">
        <ThemeToggle />
      </div>
      <AppHeader />
      <main className="flex-1">
        {children}
      </main>
      <Footer />
    </div>
  );
};

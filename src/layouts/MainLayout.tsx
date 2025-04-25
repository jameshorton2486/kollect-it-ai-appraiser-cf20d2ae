
import { Outlet, Link } from "react-router-dom";
import { AppHeader } from "@/components/AppHeader";

export const MainLayout = () => {
  return (
    <div className="min-h-screen bg-background">
      <AppHeader />
      <div className="container mx-auto py-4 px-4">
        <div className="flex justify-center space-x-4 mb-6">
          <Link 
            to="/" 
            className="py-2 px-4 rounded-md hover:bg-secondary transition-colors"
          >
            Appraiser Dashboard
          </Link>
          <Link 
            to="/photo-processor" 
            className="py-2 px-4 rounded-md hover:bg-secondary transition-colors"
          >
            Photo Processor
          </Link>
        </div>
        <main className="py-6">
          <Outlet />
        </main>
      </div>
    </div>
  );
};

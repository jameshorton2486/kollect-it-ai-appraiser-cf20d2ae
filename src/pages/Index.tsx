
import { AppraiserDashboard } from "@/components/AppraiserDashboard";
import { MainLayout } from "@/components/MainLayout";

const Index = () => {
  return (
    <MainLayout>
      <div className="min-h-screen bg-background">
        <AppraiserDashboard />
      </div>
    </MainLayout>
  );
};

export default Index;


import { Card } from "@/components/ui/card";

interface AppraisalResultsProps {
  result: string | null;
}

export const AppraisalResults = ({ result }: AppraisalResultsProps) => {
  if (!result) {
    return (
      <div className="text-center text-gray-500">
        No appraisal results yet. Upload an image to get started.
      </div>
    );
  }

  return (
    <Card className="p-4">
      <div className="prose max-w-none">
        {result}
      </div>
    </Card>
  );
};

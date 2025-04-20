import { Card } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { FileText, Printer, Copy } from "lucide-react";
import { showNotification } from "@/utils/notifications";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { useIsMobile } from "@/hooks/use-mobile";

interface AppraisalResultsProps {
  result: string | null;
  metadata?: {
    model?: string;
    timestamp?: string;
    templateId?: string;
  };
  isTableView?: boolean;
  appraisals?: Array<{
    id: number;
    title: string;
    date: string;
    type: string;
    image?: string;
  }>;
  onViewAppraisal?: (id: number) => void;
}

export const AppraisalResults = ({
  result,
  metadata,
  isTableView = false,
  appraisals = [],
  onViewAppraisal,
}: AppraisalResultsProps) => {
  const isMobile = useIsMobile();

  if (isTableView && appraisals.length > 0) {
    return (
      <Card className="p-2 sm:p-4">
        <div className="overflow-x-auto">
          <Table>
            <TableHeader>
              <TableRow>
                {!isMobile && <TableHead>Image</TableHead>}
                <TableHead>Title</TableHead>
                <TableHead>Date</TableHead>
                {!isMobile && <TableHead>Type</TableHead>}
                <TableHead>Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              {appraisals.map((appraisal) => (
                <TableRow key={appraisal.id}>
                  {!isMobile && (
                    <TableCell>
                      {appraisal.image ? (
                        <div 
                          className="w-12 h-12 sm:w-16 sm:h-16 bg-cover bg-center rounded" 
                          style={{ backgroundImage: `url(${appraisal.image})` }}
                        />
                      ) : (
                        <div className="w-12 h-12 sm:w-16 sm:h-16 bg-muted flex items-center justify-center rounded">
                          <span className="text-xs text-muted-foreground">No Image</span>
                        </div>
                      )}
                    </TableCell>
                  )}
                  <TableCell className="font-medium">{appraisal.title}</TableCell>
                  <TableCell>{appraisal.date}</TableCell>
                  {!isMobile && <TableCell>{appraisal.type}</TableCell>}
                  <TableCell>
                    <Button
                      size="sm"
                      variant="secondary"
                      onClick={() => onViewAppraisal && onViewAppraisal(appraisal.id)}
                      className="w-full sm:w-auto"
                    >
                      View
                    </Button>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </div>
      </Card>
    );
  }

  if (!result) {
    return (
      <Card className="p-4 text-center">
        <p className="text-muted-foreground">
          No appraisal results yet. Upload an image to get started.
        </p>
      </Card>
    );
  }

  const handleCopy = async () => {
    try {
      await navigator.clipboard.writeText(result);
      showNotification("Appraisal copied to clipboard", "success");
    } catch (err) {
      showNotification("Failed to copy to clipboard", "error");
    }
  };

  const handleExport = () => {
    const blob = new Blob([result], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `appraisal-${new Date().toISOString().split('T')[0]}.txt`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
    showNotification("Appraisal exported successfully", "success");
  };

  const handlePrint = () => {
    const printWindow = window.open('', '_blank');
    if (!printWindow) return;

    const html = `
      <!DOCTYPE html>
      <html>
        <head>
          <title>Expert Appraisal Report</title>
          <style>
            body { font-family: Arial, sans-serif; padding: 20px; line-height: 1.6; }
            .metadata { color: #666; font-size: 0.9em; margin-bottom: 20px; }
            .content { white-space: pre-wrap; }
          </style>
        </head>
        <body>
          <h1>Expert Appraisal Report</h1>
          ${metadata ? `
            <div class="metadata">
              <p>Generated: ${new Date(metadata.timestamp || '').toLocaleString()}</p>
              ${metadata.model ? `<p>Model: ${metadata.model}</p>` : ''}
              ${metadata.templateId ? `<p>Template: ${metadata.templateId}</p>` : ''}
            </div>
          ` : ''}
          <div class="content">${result}</div>
        </body>
      </html>
    `;

    printWindow.document.write(html);
    printWindow.document.close();
    printWindow.focus();
    setTimeout(() => {
      printWindow.print();
      printWindow.close();
    }, 250);
  };

  return (
    <Card className="p-4">
      <div className="prose prose-purple max-w-none dark:prose-invert">
        {result}
      </div>
      <div className="mt-4 flex flex-wrap gap-2">
        <Button variant="outline" onClick={handleCopy} className="flex-1 sm:flex-none">
          <Copy className="mr-2 h-4 w-4" />
          Copy
        </Button>
        <Button variant="outline" onClick={handleExport} className="flex-1 sm:flex-none">
          <FileText className="mr-2 h-4 w-4" />
          Export
        </Button>
        <Button variant="outline" onClick={handlePrint} className="flex-1 sm:flex-none">
          <Printer className="mr-2 h-4 w-4" />
          Print
        </Button>
      </div>
      {metadata && (
        <div className="mt-4 text-sm text-muted-foreground space-y-1">
          <p>Generated: {new Date(metadata.timestamp || '').toLocaleString()}</p>
          {metadata.model && <p>Model: {metadata.model}</p>}
          {metadata.templateId && <p>Template: {metadata.templateId}</p>}
        </div>
      )}
    </Card>
  );
};

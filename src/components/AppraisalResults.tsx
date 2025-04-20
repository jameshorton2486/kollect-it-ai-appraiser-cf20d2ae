
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
  onViewAppraisal
}: AppraisalResultsProps) => {
  
  // If in table view mode, render the admin table
  if (isTableView && appraisals.length > 0) {
    return (
      <Card className="p-4">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Image</TableHead>
              <TableHead>Title</TableHead>
              <TableHead>Date</TableHead>
              <TableHead>Type</TableHead>
              <TableHead>Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            {appraisals.map((appraisal) => (
              <TableRow key={appraisal.id}>
                <TableCell>
                  {appraisal.image ? (
                    <div className="w-16 h-16 bg-cover bg-center rounded" 
                         style={{ backgroundImage: `url(${appraisal.image})` }}></div>
                  ) : (
                    <div className="w-16 h-16 bg-gray-100 flex items-center justify-center rounded">
                      <span className="text-xs text-gray-500">No Image</span>
                    </div>
                  )}
                </TableCell>
                <TableCell>{appraisal.title}</TableCell>
                <TableCell>{appraisal.date}</TableCell>
                <TableCell>{appraisal.type}</TableCell>
                <TableCell>
                  <Button 
                    size="sm" 
                    variant="outline" 
                    onClick={() => onViewAppraisal && onViewAppraisal(appraisal.id)}
                  >
                    View
                  </Button>
                </TableCell>
              </TableRow>
            ))}
          </TableBody>
        </Table>
      </Card>
    );
  }

  if (!result) {
    return (
      <div className="text-center text-gray-500">
        No appraisal results yet. Upload an image to get started.
      </div>
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
      <div className="prose max-w-none">
        {result}
      </div>
      <div className="mt-4 flex gap-2">
        <Button variant="outline" onClick={handleCopy}>
          <Copy className="mr-2 h-4 w-4" />
          Copy
        </Button>
        <Button variant="outline" onClick={handleExport}>
          <FileText className="mr-2 h-4 w-4" />
          Export
        </Button>
        <Button variant="outline" onClick={handlePrint}>
          <Printer className="mr-2 h-4 w-4" />
          Print
        </Button>
      </div>
      {metadata && (
        <div className="mt-4 text-sm text-gray-500">
          <p>Generated: {new Date(metadata.timestamp || '').toLocaleString()}</p>
          {metadata.model && <p>Model: {metadata.model}</p>}
          {metadata.templateId && <p>Template: {metadata.templateId}</p>}
        </div>
      )}
    </Card>
  );
};

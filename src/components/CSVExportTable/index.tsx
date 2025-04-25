
import React, { useState } from "react";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Download, FileText } from "lucide-react";
import { saveAs } from "file-saver";
import { showNotification } from "@/utils/notifications";

interface ProductData {
  id: string;
  imageName: string;
  title: string;
  description: string;
  priceRange: string;
}

interface CSVExportTableProps {
  products: ProductData[];
  onDownload: () => void;
}

export const CSVExportTable = ({ products, onDownload }: CSVExportTableProps) => {
  const [editableProducts, setEditableProducts] = useState<ProductData[]>(products);
  const [showAdditionalColumns, setShowAdditionalColumns] = useState(false);
  const [categoryField, setCategoryField] = useState("Antiques");
  const [includeCategories, setIncludeCategories] = useState(true);
  const [includeTags, setIncludeTags] = useState(true);
  const [includeSKU, setIncludeSKU] = useState(true);

  // Update products when props change
  React.useEffect(() => {
    setEditableProducts(products);
  }, [products]);

  const updateProductField = (index: number, field: keyof ProductData, value: string) => {
    const updatedProducts = [...editableProducts];
    updatedProducts[index] = {
      ...updatedProducts[index],
      [field]: value
    };
    setEditableProducts(updatedProducts);
  };

  const handleExportCSV = () => {
    try {
      // Create headers based on selected fields
      let headers = ["Title", "Description", "Regular Price", "Image"];
      
      if (includeSKU) {
        headers.push("SKU");
      }
      
      if (includeCategories) {
        headers.push("Categories");
      }
      
      if (includeTags) {
        headers.push("Tags");
      }
      
      // Create CSV content with headers
      let csvContent = headers.join(",") + "\n";
      
      // Add data rows
      editableProducts.forEach((product, index) => {
        // Extract price value from range (take the lower value)
        const priceMatch = product.priceRange.match(/\$(\d+)/);
        const price = priceMatch ? priceMatch[1] : "";
        
        // Escape fields with quotes and replace internal quotes
        const escapedTitle = `"${product.title.replace(/"/g, '""')}"`;
        const escapedDescription = `"${product.description.replace(/"/g, '""')}"`;
        
        // Base row with required fields
        let row = [
          escapedTitle,
          escapedDescription,
          price,
          product.imageName
        ];
        
        // Add optional fields if selected
        if (includeSKU) {
          const sku = `PROD-${String(index + 1).padStart(3, '0')}`;
          row.push(sku);
        }
        
        if (includeCategories) {
          row.push(`"${categoryField}"`);
        }
        
        if (includeTags) {
          // Generate tags from the title
          const tags = product.title
            .split(' ')
            .filter(word => word.length > 3)
            .slice(0, 3)
            .join(',');
          row.push(`"${tags}"`);
        }
        
        csvContent += row.join(",") + "\n";
      });
      
      // Create and download the CSV file
      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
      saveAs(blob, "products-upload-ready.csv");
      
      showNotification("CSV file exported successfully!", "success");
      onDownload();
    } catch (error) {
      showNotification(`Error exporting CSV: ${(error as Error).message}`, "error");
    }
  };

  if (!products.length) {
    return (
      <div className="text-center p-8 text-muted-foreground">
        No product data available. Generate content for products first.
      </div>
    );
  }

  return (
    <div className="space-y-4">
      <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div>
          <h3 className="text-lg font-semibold">WooCommerce Ready Export</h3>
          <p className="text-sm text-muted-foreground">
            Preview and edit your product data before exporting
          </p>
        </div>
        
        <div className="flex flex-wrap gap-2">
          <Button
            variant="outline"
            size="sm"
            onClick={() => setShowAdditionalColumns(!showAdditionalColumns)}
          >
            {showAdditionalColumns ? "Hide" : "Show"} Optional Fields
          </Button>
          
          <Button 
            variant="default" 
            size="sm"
            onClick={handleExportCSV}
          >
            <FileText className="h-4 w-4 mr-2" />
            Export to CSV
          </Button>
        </div>
      </div>
      
      {showAdditionalColumns && (
        <div className="bg-muted p-4 rounded-md mb-4">
          <h4 className="font-medium mb-3">Optional Fields</h4>
          <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div className="flex items-center space-x-2">
              <input
                type="checkbox"
                id="include-sku"
                checked={includeSKU}
                onChange={(e) => setIncludeSKU(e.target.checked)}
                className="rounded border-gray-300"
              />
              <label htmlFor="include-sku" className="text-sm">Include SKUs</label>
            </div>
            
            <div className="flex items-center space-x-2">
              <input
                type="checkbox"
                id="include-categories"
                checked={includeCategories}
                onChange={(e) => setIncludeCategories(e.target.checked)}
                className="rounded border-gray-300"
              />
              <label htmlFor="include-categories" className="text-sm">Include Categories</label>
            </div>
            
            <div className="flex items-center space-x-2">
              <input
                type="checkbox"
                id="include-tags"
                checked={includeTags}
                onChange={(e) => setIncludeTags(e.target.checked)}
                className="rounded border-gray-300"
              />
              <label htmlFor="include-tags" className="text-sm">Include Tags</label>
            </div>
          </div>
          
          {includeCategories && (
            <div className="mt-3">
              <label htmlFor="category-field" className="text-sm block mb-1">
                Default Category
              </label>
              <Input
                id="category-field"
                value={categoryField}
                onChange={(e) => setCategoryField(e.target.value)}
                placeholder="Default category"
                className="max-w-xs"
              />
            </div>
          )}
        </div>
      )}
      
      <div className="overflow-x-auto border rounded-md">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Title</TableHead>
              <TableHead>Description (excerpt)</TableHead>
              <TableHead>Regular Price</TableHead>
              <TableHead>Image</TableHead>
              {includeSKU && <TableHead>SKU</TableHead>}
              {includeCategories && <TableHead>Categories</TableHead>}
              {includeTags && <TableHead>Tags</TableHead>}
            </TableRow>
          </TableHeader>
          <TableBody>
            {editableProducts.map((product, index) => {
              // Extract price value from range for display
              const priceMatch = product.priceRange.match(/\$(\d+)/);
              const price = priceMatch ? priceMatch[1] : "";
              
              // Generate tags from the title for preview
              const tags = product.title
                .split(' ')
                .filter(word => word.length > 3)
                .slice(0, 3)
                .join(', ');
              
              return (
                <TableRow key={product.id}>
                  <TableCell className="font-medium">
                    <Input
                      value={product.title}
                      onChange={(e) => updateProductField(index, 'title', e.target.value)}
                      className="w-full"
                    />
                  </TableCell>
                  <TableCell>
                    <div className="max-w-md">
                      <Input
                        value={product.description.substring(0, 50) + "..."}
                        onChange={(e) => updateProductField(index, 'description', e.target.value)}
                        className="w-full"
                      />
                    </div>
                  </TableCell>
                  <TableCell>
                    <Input
                      value={price}
                      onChange={(e) => {
                        const newPriceRange = `$${e.target.value}`;
                        updateProductField(index, 'priceRange', newPriceRange);
                      }}
                      className="w-24"
                    />
                  </TableCell>
                  <TableCell>{product.imageName}</TableCell>
                  {includeSKU && <TableCell>{`PROD-${String(index + 1).padStart(3, '0')}`}</TableCell>}
                  {includeCategories && <TableCell>{categoryField}</TableCell>}
                  {includeTags && <TableCell>{tags}</TableCell>}
                </TableRow>
              );
            })}
          </TableBody>
        </Table>
      </div>
      
      <div className="flex justify-end mt-4">
        <Button 
          onClick={handleExportCSV}
          className="px-4"
        >
          <Download className="h-4 w-4 mr-2" />
          Download CSV
        </Button>
      </div>
    </div>
  );
};

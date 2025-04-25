
import { useState, useEffect } from "react";
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Textarea } from "@/components/ui/textarea";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Pencil, Save, Loader2, Download } from "lucide-react";
import { showNotification } from "@/utils/notifications";
import { useAIContentGenerator } from "@/hooks/useAIContentGenerator";
import JSZip from "jszip";
import { saveAs } from "file-saver";

interface AIContentGeneratorProps {
  images: Array<{id: string; processed: string; name: string;}>;
}

interface ProductContent {
  id: string;
  imageName: string;
  imageUrl: string;
  title: string;
  description: string;
  priceRange: string;
  isGenerating: boolean;
  isEditing: boolean;
}

export const AIContentGenerator = ({ images }: AIContentGeneratorProps) => {
  const [apiKey, setApiKey] = useState("");
  const [additionalNotes, setAdditionalNotes] = useState("");
  const [productBrand, setProductBrand] = useState("");
  const [productMaterial, setProductMaterial] = useState("");
  const [productPeriod, setProductPeriod] = useState("");
  const [products, setProducts] = useState<ProductContent[]>([]);
  const [isGeneratingAll, setIsGeneratingAll] = useState(false);
  
  const { generateContent, isLoading } = useAIContentGenerator();

  // Initialize products from images
  useEffect(() => {
    if (images?.length > 0) {
      const initialProducts = images.map(img => ({
        id: img.id,
        imageName: img.name,
        imageUrl: img.processed,
        title: "",
        description: "",
        priceRange: "",
        isGenerating: false,
        isEditing: false
      }));
      setProducts(initialProducts);
    }
  }, [images]);

  const handleGenerateAll = async () => {
    if (!apiKey) {
      showNotification("Please enter your OpenAI API key first", "error");
      return;
    }
    
    setIsGeneratingAll(true);
    
    const context = {
      brand: productBrand,
      material: productMaterial,
      period: productPeriod,
      notes: additionalNotes
    };
    
    try {
      // Generate content for each product one by one
      const updatedProducts = [...products];
      
      for (let i = 0; i < updatedProducts.length; i++) {
        const product = updatedProducts[i];
        product.isGenerating = true;
        setProducts([...updatedProducts]);
        
        const result = await generateContent(product.imageUrl, apiKey, context);
        
        product.isGenerating = false;
        product.title = result.title;
        product.description = result.description;
        product.priceRange = result.priceRange;
        
        setProducts([...updatedProducts]);
      }
      
      showNotification("Content generated for all products!", "success");
    } catch (error) {
      showNotification(`Error generating content: ${(error as Error).message}`, "error");
    } finally {
      setIsGeneratingAll(false);
    }
  };

  const handleGenerateSingle = async (productId: string) => {
    if (!apiKey) {
      showNotification("Please enter your OpenAI API key first", "error");
      return;
    }
    
    const context = {
      brand: productBrand,
      material: productMaterial,
      period: productPeriod,
      notes: additionalNotes
    };
    
    const updatedProducts = products.map(p => {
      if (p.id === productId) {
        return { ...p, isGenerating: true };
      }
      return p;
    });
    
    setProducts(updatedProducts);
    
    try {
      const product = products.find(p => p.id === productId);
      if (!product) return;
      
      const result = await generateContent(product.imageUrl, apiKey, context);
      
      setProducts(products.map(p => {
        if (p.id === productId) {
          return {
            ...p,
            title: result.title,
            description: result.description,
            priceRange: result.priceRange,
            isGenerating: false
          };
        }
        return p;
      }));
      
      showNotification("Content generated successfully!", "success");
    } catch (error) {
      setProducts(products.map(p => {
        if (p.id === productId) {
          return { ...p, isGenerating: false };
        }
        return p;
      }));
      showNotification(`Error generating content: ${(error as Error).message}`, "error");
    }
  };

  const toggleEdit = (productId: string) => {
    setProducts(products.map(p => {
      if (p.id === productId) {
        return { ...p, isEditing: !p.isEditing };
      }
      return p;
    }));
  };

  const updateProductField = (productId: string, field: keyof ProductContent, value: string) => {
    setProducts(products.map(p => {
      if (p.id === productId) {
        return { ...p, [field]: value };
      }
      return p;
    }));
  };

  const downloadCSV = () => {
    try {
      // Create CSV content
      let csvContent = "Image Name,Title,Description,Price Range\n";
      
      products.forEach(product => {
        // Escape quotes in text fields
        const escapedTitle = product.title.replace(/"/g, '""');
        const escapedDescription = product.description.replace(/"/g, '""');
        
        csvContent += `${product.imageName},"${escapedTitle}","${escapedDescription}","${product.priceRange}"\n`;
      });
      
      // Create a Blob with the CSV data
      const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
      saveAs(blob, "product-content.csv");
      
      showNotification("CSV file downloaded successfully!", "success");
    } catch (error) {
      showNotification(`Error downloading CSV: ${(error as Error).message}`, "error");
    }
  };

  const downloadAllData = async () => {
    try {
      const zip = new JSZip();
      
      // Add CSV file
      let csvContent = "Image Name,Title,Description,Price Range\n";
      products.forEach(product => {
        const escapedTitle = product.title.replace(/"/g, '""');
        const escapedDescription = product.description.replace(/"/g, '""');
        csvContent += `${product.imageName},"${escapedTitle}","${escapedDescription}","${product.priceRange}"\n`;
      });
      zip.file("product-content.csv", csvContent);
      
      // Add images
      products.forEach((product, index) => {
        // Extract base64 image data
        const imageData = product.imageUrl.split(',')[1];
        const paddedIndex = String(index + 1).padStart(2, '0');
        const extension = product.imageUrl.includes('image/webp') ? 'webp' : 'jpg';
        const fileName = `product-image-${paddedIndex}.${extension}`;
        
        zip.file(fileName, imageData, { base64: true });
      });
      
      // Generate and download the zip
      const content = await zip.generateAsync({ type: "blob" });
      saveAs(content, "product-content-and-images.zip");
      
      showNotification("All data downloaded successfully!", "success");
    } catch (error) {
      showNotification(`Error downloading data: ${(error as Error).message}`, "error");
    }
  };

  return (
    <div className="space-y-6">
      {/* API Key and Context Input */}
      <Card className="p-4">
        <CardHeader>
          <CardTitle>AI Content Generation Settings</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div>
            <Label htmlFor="openai-api-key">OpenAI API Key (Required)</Label>
            <Input
              id="openai-api-key"
              type="password"
              placeholder="Enter your OpenAI API key"
              value={apiKey}
              onChange={(e) => setApiKey(e.target.value)}
            />
            <p className="text-xs text-muted-foreground mt-1">
              Your API key is required to generate product content
            </p>
          </div>
          
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <Label htmlFor="product-brand">Brand (Optional)</Label>
              <Input
                id="product-brand"
                placeholder="Brand name"
                value={productBrand}
                onChange={(e) => setProductBrand(e.target.value)}
              />
            </div>
            
            <div>
              <Label htmlFor="product-material">Material (Optional)</Label>
              <Input
                id="product-material"
                placeholder="Main material"
                value={productMaterial}
                onChange={(e) => setProductMaterial(e.target.value)}
              />
            </div>
            
            <div>
              <Label htmlFor="product-period">Period/Era (Optional)</Label>
              <Input
                id="product-period"
                placeholder="e.g., Mid-Century, Art Deco"
                value={productPeriod}
                onChange={(e) => setProductPeriod(e.target.value)}
              />
            </div>
          </div>
          
          <div>
            <Label htmlFor="additional-notes">Additional Notes (Optional)</Label>
            <Textarea
              id="additional-notes"
              placeholder="Add any known details about the products"
              value={additionalNotes}
              onChange={(e) => setAdditionalNotes(e.target.value)}
              rows={3}
            />
          </div>
        </CardContent>
        <CardFooter className="flex justify-between">
          <Button
            variant="default"
            onClick={handleGenerateAll}
            disabled={isGeneratingAll || !apiKey || products.length === 0}
          >
            {isGeneratingAll && <Loader2 className="h-4 w-4 mr-2 animate-spin" />}
            Generate All Content
          </Button>
          
          <div className="space-x-2">
            <Button
              variant="outline"
              onClick={downloadCSV}
              disabled={products.some(p => !p.title)}
            >
              <Download className="h-4 w-4 mr-2" />
              Download CSV
            </Button>
            
            <Button
              variant="secondary"
              onClick={downloadAllData}
              disabled={products.some(p => !p.title)}
            >
              <Download className="h-4 w-4 mr-2" />
              Download All
            </Button>
          </div>
        </CardFooter>
      </Card>
      
      {/* Product Content Cards */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {products.map((product) => (
          <Card key={product.id} className="overflow-hidden">
            <div className="aspect-square relative">
              <img 
                src={product.imageUrl} 
                alt={product.title || "Product"} 
                className="object-contain w-full h-full"
              />
            </div>
            
            <CardContent className="p-4 space-y-4">
              {/* Title */}
              <div>
                <div className="flex justify-between items-center">
                  <Label className="text-sm font-medium">Product Title</Label>
                  {product.title && !product.isEditing && (
                    <Button variant="ghost" size="sm" onClick={() => toggleEdit(product.id)}>
                      <Pencil className="h-3 w-3" />
                    </Button>
                  )}
                </div>
                
                {product.isEditing ? (
                  <div className="mt-1 flex gap-2">
                    <Input
                      value={product.title}
                      onChange={(e) => updateProductField(product.id, 'title', e.target.value)}
                      className="flex-grow"
                    />
                    <Button size="sm" onClick={() => toggleEdit(product.id)}>
                      <Save className="h-4 w-4" />
                    </Button>
                  </div>
                ) : (
                  <p className="mt-1 text-lg font-medium">
                    {product.title || "Not generated yet"}
                  </p>
                )}
              </div>
              
              {/* Description */}
              <div>
                <div className="flex justify-between items-center">
                  <Label className="text-sm font-medium">Product Description</Label>
                  {product.description && !product.isEditing && (
                    <Button variant="ghost" size="sm" onClick={() => toggleEdit(product.id)}>
                      <Pencil className="h-3 w-3" />
                    </Button>
                  )}
                </div>
                
                {product.isEditing ? (
                  <div className="mt-1 flex gap-2">
                    <Textarea
                      value={product.description}
                      onChange={(e) => updateProductField(product.id, 'description', e.target.value)}
                      className="flex-grow"
                      rows={6}
                    />
                    <Button size="sm" onClick={() => toggleEdit(product.id)}>
                      <Save className="h-4 w-4" />
                    </Button>
                  </div>
                ) : (
                  <p className="mt-1 text-sm text-muted-foreground whitespace-pre-wrap">
                    {product.description || "Not generated yet"}
                  </p>
                )}
              </div>
              
              {/* Price Range */}
              <div>
                <div className="flex justify-between items-center">
                  <Label className="text-sm font-medium">Value Range</Label>
                  {product.priceRange && !product.isEditing && (
                    <Button variant="ghost" size="sm" onClick={() => toggleEdit(product.id)}>
                      <Pencil className="h-3 w-3" />
                    </Button>
                  )}
                </div>
                
                {product.isEditing ? (
                  <div className="mt-1 flex gap-2">
                    <Input
                      value={product.priceRange}
                      onChange={(e) => updateProductField(product.id, 'priceRange', e.target.value)}
                      className="flex-grow"
                    />
                    <Button size="sm" onClick={() => toggleEdit(product.id)}>
                      <Save className="h-4 w-4" />
                    </Button>
                  </div>
                ) : (
                  <p className="mt-1 text-lg font-medium text-primary">
                    {product.priceRange || "Not generated yet"}
                  </p>
                )}
              </div>
            </CardContent>
            
            <CardFooter className="p-4 pt-0">
              {!product.title ? (
                <Button
                  className="w-full"
                  onClick={() => handleGenerateSingle(product.id)}
                  disabled={product.isGenerating || !apiKey}
                >
                  {product.isGenerating ? (
                    <>
                      <Loader2 className="h-4 w-4 mr-2 animate-spin" />
                      Generating...
                    </>
                  ) : (
                    "Generate Content"
                  )}
                </Button>
              ) : (
                <Button
                  variant="outline" 
                  className="w-full"
                  onClick={() => handleGenerateSingle(product.id)}
                  disabled={product.isGenerating || !apiKey}
                >
                  {product.isGenerating ? (
                    <>
                      <Loader2 className="h-4 w-4 mr-2 animate-spin" />
                      Regenerating...
                    </>
                  ) : (
                    "Regenerate Content"
                  )}
                </Button>
              )}
            </CardFooter>
          </Card>
        ))}
      </div>
    </div>
  );
};

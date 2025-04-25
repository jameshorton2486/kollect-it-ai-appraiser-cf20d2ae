import { useState, useEffect } from "react";
import { Card, CardContent, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Textarea } from "@/components/ui/textarea";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Pencil, Save, Loader2, Download, FileText } from "lucide-react";
import { showNotification } from "@/utils/notifications";
import { useAIContentGenerator } from "@/hooks/useAIContentGenerator";
import { CSVExportTable } from "@/components/CSVExportTable";
import JSZip from "jszip";
import { saveAs } from "file-saver";

interface AIContentGeneratorProps {
  images: Array<{id: string; processed: string; name: string;}>;
}

export interface ProductContent {
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
  const [activeTab, setActiveTab] = useState<string>("generate");
  const [apiKey, setApiKey] = useState("");
  const [additionalNotes, setAdditionalNotes] = useState("");
  const [productBrand, setProductBrand] = useState("");
  const [productMaterial, setProductMaterial] = useState("");
  const [productPeriod, setProductPeriod] = useState("");
  const [products, setProducts] = useState<ProductContent[]>([]);
  const [isGeneratingAll, setIsGeneratingAll] = useState(false);
  
  const { generateContent, isLoading } = useAIContentGenerator();

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

  const downloadCSV = (format: 'simple' | 'advanced' = 'simple') => {
    try {
      let headers = format === 'simple' 
        ? "post_title,post_content,regular_price,images\n"
        : "post_title,post_content,regular_price,images,sku,categories,tags\n";
      
      products.forEach((product, index) => {
        const priceMatch = product.priceRange.match(/\$(\d+)/);
        const price = priceMatch ? priceMatch[1] : "";
        
        const escapedTitle = product.title.replace(/"/g, '""');
        const escapedDescription = product.description.replace(/"/g, '""');
        
        let row = `"${escapedTitle}","${escapedDescription}",${price},${product.imageName}`;
        
        if (format === 'advanced') {
          const sku = `PROD-${String(index + 1).padStart(3, '0')}`;
          const tags = product.title
            .split(' ')
            .filter(word => word.length > 3)
            .slice(0, 3)
            .join(',');
          
          row += `,${sku},"Antiques","${tags}"`;
        }
        
        headers += row + "\n";
      });
      
      const blob = new Blob([headers], { type: 'text/csv;charset=utf-8;' });
      const filename = format === 'simple' ? "products-simple.csv" : "products-advanced.csv";
      saveAs(blob, filename);
      
      showNotification(`${format === 'simple' ? 'Simple' : 'Advanced'} CSV file downloaded successfully!`, "success");
    } catch (error) {
      showNotification(`Error downloading CSV: ${(error as Error).message}`, "error");
    }
  };

  const downloadAllData = async () => {
    try {
      const zip = new JSZip();
      
      let csvContent = "Title,Description,Regular Price,Image,SKU,Categories,Tags\n";
      products.forEach((product, index) => {
        const escapedTitle = product.title.replace(/"/g, '""');
        const escapedDescription = product.description.replace(/"/g, '""');
        
        const priceMatch = product.priceRange.match(/\$(\d+)/);
        const price = priceMatch ? priceMatch[1] : "";
        
        const sku = `PROD-${String(index + 1).padStart(3, '0')}`;
        
        const tags = product.title
          .split(' ')
          .filter(word => word.length > 3)
          .slice(0, 3)
          .join(',');
        
        csvContent += `"${escapedTitle}","${escapedDescription}",${price},${product.imageName},${sku},"Antiques","${tags}"\n`;
      });
      zip.file("products-upload-ready.csv", csvContent);
      
      products.forEach((product, index) => {
        const imageData = product.imageUrl.split(',')[1];
        const paddedIndex = String(index + 1).padStart(2, '0');
        const extension = product.imageUrl.includes('image/webp') ? 'webp' : 'jpg';
        const fileName = `product-image-${paddedIndex}.${extension}`;
        
        zip.file(fileName, imageData, { base64: true });
      });
      
      const content = await zip.generateAsync({ type: "blob" });
      saveAs(content, "product-content-and-images.zip");
      
      showNotification("All data downloaded successfully!", "success");
    } catch (error) {
      showNotification(`Error downloading data: ${(error as Error).message}`, "error");
    }
  };

  const goToExportTab = () => {
    setActiveTab("export");
  };

  return (
    <div className="space-y-6">
      <Tabs value={activeTab} onValueChange={setActiveTab} className="w-full">
        <TabsList className="grid grid-cols-2 mb-4">
          <TabsTrigger value="generate">Generate Content</TabsTrigger>
          <TabsTrigger value="export" disabled={products.some(p => !p.title)}>
            Export for WordPress
          </TabsTrigger>
        </TabsList>
        
        <TabsContent value="generate" className="space-y-6">
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
                  onClick={() => setActiveTab("export")}
                  disabled={products.some(p => !p.title)}
                >
                  <FileText className="h-4 w-4 mr-2" />
                  Prepare for Export
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
        </TabsContent>
        
        <TabsContent value="export">
          <Card>
            <CardHeader>
              <CardTitle>WordPress / WooCommerce Export</CardTitle>
            </CardHeader>
            <CardContent>
              <CSVExportTable 
                products={products.map(p => ({
                  id: p.id,
                  imageName: p.imageName,
                  title: p.title,
                  description: p.description,
                  priceRange: p.priceRange
                }))}
                onDownload={() => {
                  showNotification("CSV file ready for WordPress import!", "success");
                }}
              />
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  );
};

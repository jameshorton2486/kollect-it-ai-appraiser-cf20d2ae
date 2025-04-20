
import axios from 'axios';

export interface AppraisalResult {
  appraisalText: string;
  error?: string;
}

export const generateAppraisal = async (
  imageData: string,
  apiKey: string
): Promise<AppraisalResult> => {
  try {
    const base64Image = imageData.includes('base64,')
      ? imageData.split('base64,')[1]
      : imageData;
    
    const response = await axios.post(
      'https://api.openai.com/v1/chat/completions',
      {
        model: 'gpt-4o-mini', // Using the recommended model that supports vision
        messages: [
          {
            role: 'user',
            content: [
              { type: 'text', text: getAppraisalPrompt() },
              {
                type: 'image_url',
                image_url: {
                  url: `data:image/jpeg;base64,${base64Image}`
                }
              }
            ]
          }
        ],
        max_tokens: 4000
      },
      {
        headers: {
          'Authorization': `Bearer ${apiKey}`,
          'Content-Type': 'application/json'
        },
        timeout: 60000
      }
    );
    
    return {
      appraisalText: response.data.choices[0].message.content
    };
  } catch (error) {
    console.error('API error:', error);
    return {
      appraisalText: '',
      error: error instanceof Error ? error.message : 'Unknown error occurred'
    };
  }
};

const getAppraisalPrompt = (): string => {
  return `You are an expert appraiser with over 30 years of experience holding the highest designations from the following organizations: AAA, ISA, ASA, and the Asheford Institute of Antiques.
  
  Please provide a comprehensive appraisal that covers:
  
  1. Item Identification with specific details.
  2. Estimated era, period, or date of creation.
  3. Artistic style and historical significance.
  4. Detailed condition assessment.
  5. Materials and construction techniques used.
  6. Provenance details (if identifiable).
  7. Current market value estimate (acceptable as a range).
  8. Comparable items recently sold with sale details.
  9. Detailed citations and footnotes from reputable sources.
  
  Format your response clearly into labeled sections with 5-7 footnotes properly cited from credible web sources.`;
};

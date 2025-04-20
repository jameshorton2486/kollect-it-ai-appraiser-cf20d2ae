
import { Card } from "@/components/ui/card";
import { LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from 'recharts';

const dummyData = [
  { date: '2024-04-14', requests: 24 },
  { date: '2024-04-15', requests: 35 },
  { date: '2024-04-16', requests: 18 },
  { date: '2024-04-17', requests: 42 },
  { date: '2024-04-18', requests: 31 },
  { date: '2024-04-19', requests: 28 },
  { date: '2024-04-20', requests: 45 },
];

export const UsageStats = () => {
  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-2xl font-bold mb-4">Usage Statistics</h2>
        <p className="text-muted-foreground mb-4">
          Monitor your API usage and request patterns.
        </p>
      </div>

      <Card className="p-6">
        <div className="h-[400px]">
          <ResponsiveContainer width="100%" height="100%">
            <LineChart data={dummyData}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="date" />
              <YAxis />
              <Tooltip />
              <Line type="monotone" dataKey="requests" stroke="#9b87f5" />
            </LineChart>
          </ResponsiveContainer>
        </div>
      </Card>
    </div>
  );
};

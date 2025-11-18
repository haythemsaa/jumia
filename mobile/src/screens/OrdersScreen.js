import React, { useState, useEffect } from 'react';
import { View, Text, FlatList, StyleSheet } from 'react-native';
import ApiService from '../services/api.service';
import { formatPrice, formatDate } from '../utils/helpers';
import { Colors, Spacing, FontSizes, BorderRadius } from '../config/theme';

const OrdersScreen = () => {
  const [orders, setOrders] = useState([]);

  useEffect(() => { loadOrders(); }, []);

  const loadOrders = async () => {
    try {
      const data = await ApiService.getOrders();
      setOrders(data.data || []);
    } catch (error) {}
  };

  const renderOrder = ({ item }) => (
    <View style={styles.orderCard}>
      <Text style={styles.orderNumber}>#{item.order_number}</Text>
      <Text style={styles.orderDate}>{formatDate(item.created_at)}</Text>
      <Text style={styles.orderTotal}>{formatPrice(item.total)}</Text>
      <View style={[styles.statusBadge, { backgroundColor: getStatusColor(item.status) }]}>
        <Text style={styles.statusText}>{item.status}</Text>
      </View>
    </View>
  );

  const getStatusColor = (status) => ({ pending: Colors.warning, confirmed: Colors.info, delivered: Colors.success }[status] || Colors.gray);

  return (
    <View style={{ flex: 1, backgroundColor: Colors.background }}>
      <FlatList data={orders} renderItem={renderOrder} keyExtractor={item => item.id.toString()} contentContainerStyle={styles.list} />
    </View>
  );
};

const styles = StyleSheet.create({
  list: { padding: Spacing.md },
  orderCard: { backgroundColor: Colors.white, borderRadius: BorderRadius.lg, padding: Spacing.lg, marginBottom: Spacing.md, shadowColor: '#000', shadowOffset: { width: 0, height: 2 }, shadowOpacity: 0.1, shadowRadius: 4, elevation: 2 },
  orderNumber: { fontSize: FontSizes.lg, fontWeight: 'bold', color: Colors.textPrimary },
  orderDate: { fontSize: FontSizes.sm, color: Colors.textSecondary, marginVertical: Spacing.xs },
  orderTotal: { fontSize: FontSizes.xl, fontWeight: 'bold', color: Colors.primary, marginTop: Spacing.sm },
  statusBadge: { marginTop: Spacing.sm, paddingHorizontal: Spacing.md, paddingVertical: Spacing.xs, borderRadius: BorderRadius.sm, alignSelf: 'flex-start' },
  statusText: { color: Colors.white, fontSize: FontSizes.sm, fontWeight: '600' },
});

export default OrdersScreen;

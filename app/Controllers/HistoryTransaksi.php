<?php

namespace App\Controllers;

use App\Models\HistoryTransaksiModel;
use App\Controllers\BaseController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as WriterXlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class HistoryTransaksi extends BaseController
{
    public $historyModel;
    public function __construct()
    {
        $this->historyModel = new HistoryTransaksiModel();
    }
    public function index()
    {
        if (session()->get('tb_user') == null) {
            return redirect()->to('/login');
        }

        $status = ['checkin', 'checkout'];
        $start = $this->request->getGet('min');
        $end = $this->request->getGet('max');
        $start = $this->getMinDateFromRequest();
        // $start = date('Y-m-d', strtotime($starttmp));
        // $end = date('Y-m-d', strtotime($endtmp));
        // Validate the date format before using them
        $start = $this->isValidDate($start) ? $start : date('Y-m-d');
        $end = $this->isValidDate($end) ? $end : date('Y-m-d');
        // Use the updated date range in your existing logic
        // $dateRange = ['min' => $start, 'max' => $end];
        // Call the model function to get filtered data
        $transaksiData = $this->historyModel->getCheckin($status[0], $start);
        $transaksiData2 = $this->historyModel->getCheckout($status[1], $start);
//         echo '<pre>';
// var_dump($transaksiData2);
// echo '</pre>';
        $transaksiData3 = $this->historyModel->getCheckout($status[1], $start); 
        $adjustmentData = $this->historyModel->getAdjustment($start);
        $returData = $this->historyModel->getRetur($status[0], $start);
        $combinedTransactions = [];

        foreach ($adjustmentData as $adjustment) {
            $transMetadataJson = $adjustment['trans_metadata'];

            // Decode the JSON string into an array
            $transMetadataArray = json_decode($transMetadataJson, true);

            // Concatenate the transactions to the combined array
            $combinedTransactions = array_merge($combinedTransactions, $transMetadataArray);
        }
        // foreach ($adjustmentData as $row) {
        //     $adjustmentRow = json_decode($row['trans_metadata'], true);

        //     // Check if $transaksi is an array before proceeding
        //     if (is_array($adjustmentRow)) {
        //         // Update the 'trans_metadata' field with the decoded array
        //         $row['trans_metadata'] = $adjustmentRow;
        //     }
        // }
        // echo '<pre>';
        // var_dump($combinedTransactions);
        // echo '</pre>';

        $this->decodeAndMergeMetadata($transaksiData);
        $this->decodeAndMergeMetadata($transaksiData2);
        $this->decodeAndMergeMetadata($transaksiData3);
        $this->decodeAndMergeMetadata($returData);
        $this->decodeAndMergeMetadata($adjustmentData);




        $data = [
            'title' => 'History Transaksi',
            'historyCheckin' => $transaksiData,
            'historyCheckout' => $transaksiData2,
            'historyCheckout' => $transaksiData3,

            'historyAdjustment' => $combinedTransactions,
            'historyRetur' => $returData,
            'start' => $start,
        ];
        echo view('historyTransaksiView', $data);
    }

    private function isValidDate($date)
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
    private function decodeAndMergeMetadata(&$data)
    {
        // foreach ($data as &$row) {
        //     $transaksi = json_decode($row['trans_metadata'], true);

        //     if (is_array($transaksi)) {
        //         $row = array_merge($row, array_reverse($transaksi, true));
        //     }
        // }
        foreach ($data as &$row) {
            $transaksi = json_decode($row['trans_metadata'], true);
            $row = array_reverse($transaksi, true);

            // Check if $transaksi is an array before pushing it back
            if (is_array($transaksi)) {
                // Merge the decoded data with the original row data
                $row = array_merge($row, $transaksi);
            }
        }
    }

    public function update()
    {
        $data = $this->request->getPost();
        $post = $this->historyModel->protect(false)->insert($data, false);

        if ($post) {
            return redirect()->route('history');
        }
        return redirect()->route('history');
    }
    // public function deleteCheckout($status, $minDate)
    // {
    //     try {
    //         // Begin transaction
    //         $this->db->transBegin();

    //         // Step 1: Get checkin entities to be deleted
    //         $checkoutsToDelete = $this->db->table('transaksi_history')
    //             ->select('id') // Select only the IDs for deletion
    //             ->where("trans_metadata LIKE '%\"status\":\"$status\"%'")
    //             ->where("trans_metadata LIKE '%\"tgl_ci\":\"$minDate%'")
    //             ->get()
    //             ->getResultArray();

    //         // Step 2: Delete each checkin entity
    //         foreach ($checkoutsToDelete as $checkout) {
    //             $this->deleteCheckoutEntity($checkout['id']);
    //         }

    //         // Commit transaction if everything is successful
    //         $this->db->transCommit();

    //         return true;
    //     } catch (\Exception $e) {
    //         // Rollback transaction if an error occurs
    //         $this->db->transRollback();
    //         echo "Error: " . $e->getMessage();
    //         return false;
    //     }
    // }
    public function delete($idTransaksi)
    {
        $delete = $this->HistoryTransaksiModel->where("trans_metadata LIKE '$idTransaksi'")->delete();
        if ($delete) {
            session()->setFlashdata("success", "History berhasil dihapus!");
        } else {
            session()->setFlashdata("fail", "History gagal dihapus!");
        }
        return redirect()->route('history');
    }
    // public function delete($id)
    // {
    //     $this->historyModel->hapusHistory($id);
    //     return redirect()->to('/history');
    // }
    // public function deleteTransaction($checkout, $status)
    // {
    //     $model = new HistoryTransaksiModel(); // Ganti dengan nama model yang sesuai

    //     // Panggil metode deleteTransaction di dalam model
    //     $result = $model->deleteTransaction($checkout, $status);

    //     if ($result) {
    //         // Handle jika berhasil dihapus
    //         return redirect()->to('your_success_route')->with('success', 'Transaction deleted successfully');
    //     } else {
    //         // Handle jika terjadi kesalahan
    //         return redirect()->to('your_error_route')->with('error', 'Error deleting transaction');
    //     }
    // }
   
    public function search()
    {
        if (isset($_GET['cari'])) {
            $cari = $_GET['cari'];
            echo "<b>Hasil pencarian : " . $cari . "</b>";
        }
    }
    private function formatDate($date)
    {
        if (!$date) {
            return ''; // Return an empty string if the date is not provided
        }

        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $months = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        $timestamp = strtotime($date);
        $formattedDate = $days[date('w', $timestamp)] . ', ' . date('d', $timestamp) . ' ' . $months[date('n', $timestamp) - 1] . ' ' . date('Y', $timestamp);

        return $formattedDate;
    }
    public function exportCheckin()
    {
        // Use the current date to generate the filename
        $currentDate = date('Y-m-d');
        $formattedDate = $this->formatDate($currentDate);
        $status = ['checkin', 'checkout', 'adjustment'];
        $starttmp = strval($this->request->getGet('min'));
        $start = date('Y-m-d', strtotime($starttmp));
        $start = $this->isValidDate($start) ? $start : date('Y-m-d');
        // echo '<pre>';
        // var_dump($starttmp);
        // echo '</pre>';
        // Fetch the data
        $transaksiData = $this->historyModel->getCheckin($status[0], $start);
        foreach ($transaksiData as &$transaksiRow) {
            $transaksi = json_decode($transaksiRow['trans_metadata'], true);

            // Check if $transaksi is an array before pushing it back
            if (is_array($transaksi)) {
                // Merge the decoded data with the original row data
                $transaksiRow = array_merge($transaksiRow, $transaksi);
                $transaksiRow = array_reverse($transaksiRow, true);
            }
        }
        $writer = $this->export($transaksiData, null, null, 'dataCheckin');

        // Construct the filename with the formatted date
        $fileName = $formattedDate . '.xlsx';
        $file = $writer->save($fileName);

        return $this->response->download($fileName, $file, true)->setFileName($fileName);
    }
    // Private method to retrieve min date from the request
    private function getMinDateFromRequest()
    {
        return $this->request->getGet('min');
    }
    public function exportCheckout()
    {
        // Use the current date to generate the filename
        $currentDate = date('Y-m-d');
        $formattedDate = $this->formatDate($currentDate);
        $status = ['checkin', 'checkout', 'adjustment'];
        $starttmp = strval($this->request->getGet('min'));
        $start = date('Y-m-d', strtotime($starttmp));
        $start = $this->isValidDate($start) ? $start : date('Y-m-d');
        echo '<pre>';
        var_dump($starttmp);
        echo '</pre>';

        // Fetch the data
        $transaksiData = $this->historyModel->getCheckout($status[1], $start);
        foreach ($transaksiData as &$transaksiRow) {
            $transaksi = json_decode($transaksiRow['trans_metadata'], true);

            // Check if $transaksi is an array before pushing it back
            if (is_array($transaksi)) {
                // Merge the decoded data with the original row data
                $transaksiRow = array_merge($transaksiRow, $transaksi);
                $transaksiRow = array_reverse($transaksiRow, true);
            }
        }
        // $writer = $this->export(null, $transaksiData, null, 'dataCheckout');

        // // Construct the filename with the formatted date
        // $fileName = $formattedDate . '.xlsx';
        // $file = $writer->save($fileName);

        // return $this->response->download($fileName, $file, true)->setFileName($fileName);
    }
    public function exportAllData()
    {
        // Use the current date to generate the filename
        $currentDate = date('Y-m-d');
        $formattedDate = $this->formatDate($currentDate);
        $status = ['checkin', 'checkout', 'adjustment', 'retur'];
        $starttmp = strval($this->request->getGet('min'));
        $start = date('Y-m-d', strtotime($starttmp));
        $start = $this->isValidDate($start) ? $start : date('Y-m-d');

        // Fetch the data
        $checkinData = $this->historyModel->getCheckin($status[0], $start);
        $checkoutData = $this->historyModel->getCheckout($status[1], $start);
        $adjustmentData = $this->historyModel->getAdjustment($start);
        $returData = $this->historyModel->getRetur($status[0], $start);

        foreach ($checkinData as &$checkinRow) {
            $checkin = json_decode($checkinRow['trans_metadata'], true);

            // Check if $transaksi is an array before pushing it back
            if (is_array($checkin)) {
                // Merge the decoded data with the original row data
                $checkinRow = array_merge($checkinRow, $checkin);
                $checkinRow = array_reverse($checkinRow, true);
            }
        }

        foreach ($checkoutData as &$checkoutRow) {
            $checkout = json_decode($checkoutRow['trans_metadata'], true);

            // Check if $transaksi is an array before pushing it back
            if (is_array($checkout)) {
                // Merge the decoded data with the original row data
                $checkoutRow = array_merge($checkoutRow, $checkout);
                $checkoutRow = array_reverse($checkoutRow, true);
            }
        }
        foreach ($adjustmentData as &$adjustmentRow) {
            $adjustment = json_decode($adjustmentRow['trans_metadata'], true);

            // Check if $transaksi is an array before pushing it back
            if (is_array($adjustment)) {
                // Merge the decoded data with the original row data
                $adjustmentRow = array_merge($adjustmentRow, $adjustment);
                $adjustmentRow = array_reverse($adjustmentRow, true);
            }
        }
        foreach ($returData as &$returRow) {
            $retur = json_decode($returRow['trans_metadata'], true);

            // Check if $transaksi is an array before pushing it back
            if (is_array($retur)) {
                // Merge the decoded data with the original row data
                $returRow = array_merge($returRow, $retur);
                $returRow = array_reverse($returRow, true);
            }
        }

        // Create a PhpSpreadsheet object for the Excel file
        $spreadsheet = new Spreadsheet();

        // Export check-in data to the first sheet
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Checkin');
        $this->export($checkinData, null, null, 'dataCheckin', $sheet1);

        // Export check-out data to the second sheet
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Checkout');
        $this->export(null, $checkoutData, null, 'dataCheckout', $sheet2);

        // Export adjustment data to the third sheet
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Adjustment');
        $this->export(null, null, $adjustmentData, 'dataAdjustment', $sheet3);

         // Export adjustment data to the third sheet
         $sheet3 = $spreadsheet->createSheet();
         $sheet3->setTitle('Retur');
         $this->export(null, null, $returData, 'dataRetur', $sheet3);

        // Save the Excel file
        $fileName = $formattedDate . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $file = $writer->save($fileName);

        return $this->response->download($fileName, $file, true)->setFileName($fileName);
    }

    public function export($dataCheckin, $dataCheckout, $dataAdjustment, $dataRetur, $jenis, $sheet)
    {
        $borderstyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
        ];

        $sheet->setCellValue('A1', 'Unique ID Scan');
        $sheet->setCellValue('B1', 'NO Lot');
        $sheet->setCellValue('C1', 'Part Number');
        $sheet->setCellValue('D1', 'Rak');
        $sheet->setCellValue('E1', 'PIC');
        $sheet->setCellValue('F1', 'Status');
        $sheet->setCellValue('G1', 'Quantity');
        $sheet->setCellValue('H1', 'Tanggal Checkin');
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A1:H1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('FFFF00');

        foreach (range('A', 'H') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $column = 2;

        if ($jenis == 'dataCheckin') {
            foreach ($dataCheckin as $checkin) {
                if (!empty($checkin)) {
                    $sheet->setCellValue('A' . $column, $checkin['unique_scanid']);
                    $sheet->setCellValue('B' . $column, $checkin['lot']);
                    $sheet->setCellValue('C' . $column, $checkin['part_number']);
                    $sheet->setCellValue('D' . $column, $checkin['kode_rak']);
                    $sheet->setCellValue('E' . $column, $checkin['pic']);
                    $sheet->setCellValue('F' . $column, $checkin['status']);
                    $sheet->setCellValue('G' . $column, $checkin['quantity']);
                    $sheet->setCellValue('H' . $column, date('d-M-Y', strtotime($checkin['tgl_ci'])));
                    $sheet->getStyle('A1:H' . $column)->applyFromArray($borderstyle);
                    $column++;
                }
            }
        } elseif ($jenis == 'dataCheckout') {
            foreach ($dataCheckout as $checkout) {
                if (!empty($checkout)) {
                    $sheet->setCellValue('A' . $column, $checkout['unique_scanid']);
                    $sheet->setCellValue('B' . $column, $checkout['lot']);
                    $sheet->setCellValue('C' . $column, $checkout['part_number']);
                    $sheet->setCellValue('D' . $column, $checkout['kode_rak']);
                    $sheet->setCellValue('E' . $column, $checkout['pic']);
                    $sheet->setCellValue('F' . $column, $checkout['status']);
                    $sheet->setCellValue('G' . $column, $checkout['quantity']);
                    $sheet->setCellValue('H' . $column, date('d-M-Y', strtotime($checkout['tgl_co'])));
                    $sheet->getStyle('A1:H' . $column)->applyFromArray($borderstyle);
                    $column++;
                }
            }
        } elseif ($jenis == 'dataAdjustment') {
            foreach ($dataAdjustment as $adjustment) {
                foreach ($adjustment as $adjust) {
                    // return dd($adjust);
                    if (is_array($adjust) && !empty($adjust)) {
                        $sheet->setCellValue('A' . $column, $adjust['unique_scanid'] ?? ''); // Use the null coalescing operator to handle the case when the key is not present
                        $sheet->setCellValue('B' . $column, $adjust['lot'] ?? '');
                        $sheet->setCellValue('C' . $column, $adjust['part_number'] ?? '');
                        $sheet->setCellValue('D' . $column, $adjust['kode_rak'] ?? '');
                        $sheet->setCellValue('E' . $column, $adjust['pic'] ?? '');
                        $sheet->setCellValue('F' . $column, $adjust['status'] ?? '');
                        $sheet->setCellValue('G' . $column, $adjust['quantity'] ?? '');
                        $sheet->setCellValue('H' . $column, date('d-M-Y', strtotime($adjust['tgl_adjust'] ?? '')));
                        $sheet->getStyle('A1:H' . $column)->applyFromArray($borderstyle);
                        $column++;
                    }
                }
            }
        } elseif ($jenis == 'dataRetur') {
            foreach ($dataRetur as $retur) {
                foreach ($retur as $return) {
                    // return dd($adjust);
                    if (is_array($return) && !empty($return)) {
                        $sheet->setCellValue('A' . $column, $return['unique_scanid'] ?? ''); // Use the null coalescing operator to handle the case when the key is not present
                        $sheet->setCellValue('B' . $column, $return['lot'] ?? '');
                        $sheet->setCellValue('C' . $column, $return['part_number'] ?? '');
                        $sheet->setCellValue('D' . $column, $return['kode_rak'] ?? '');
                        $sheet->setCellValue('E' . $column, $return['pic'] ?? '');
                        $sheet->setCellValue('F' . $column, $return['status'] ?? '');
                        $sheet->setCellValue('G' . $column, $return['quantity'] ?? '');
                        $sheet->setCellValue('H' . $column, date('d-M-Y', strtotime($return['tgl_adjust'] ?? '')));
                        $sheet->getStyle('A1:H' . $column)->applyFromArray($borderstyle);
                        $column++;
                    }
                }
            }
        }


        return $sheet;
    }
}

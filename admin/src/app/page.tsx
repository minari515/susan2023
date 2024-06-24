// pages/index.tsx
"use client";

import { useState } from 'react';
import FullCalendar, { DateClickArg } from '@fullcalendar/react';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import { saveAs } from 'file-saver';
import styles from './Home.module.css';

const HomePage = () => {
  const [selection, setSelection] = useState<string | null>(null);
  const [events, setEvents] = useState<{ start: string; end: string }[]>([]);
  const [startDate, setStartDate] = useState<string | null>(null);
  const [counter, setCounter] = useState<number>(1); // カウンターの初期値を設定

  const handleSelectionChange = (value: string) => {
    setSelection(value);
  };

  const handleDateClick = (dateClickInfo: DateClickArg) => {
    const clickedDate = dateClickInfo.dateStr;

    if (startDate) {
      const newEvent = {
        start: startDate,
        end: clickedDate,
      };
      setEvents((prevEvents) => [...prevEvents, newEvent]);
      setStartDate(null);
    } else {
      setStartDate(clickedDate);
    }
  };

  const handleSaveJson = () => {
    if (selection && events.length > 0) {
      const data = {
        selection,
        periods: events,
      };
      const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
      saveAs(blob, 'data.json');
    } else {
      alert('未選択の項目があります。すべて選択してから実行してください');
    }
  };

  const handleCancelSelection = () => {
    setEvents([]); // 選択された範囲を空にする
  };

  return (
    <div className={styles.container}>
      <h1>Data Selection</h1>
      <div className={styles.selectionButtons}>
        <button
          className={selection === '誘い' ? styles.selectedButton : ''}
          onClick={() => handleSelectionChange('誘い')}
        >
          誘い
        </button>
        <button
          className={selection === '入門' ? styles.selectedButton : ''}
          onClick={() => handleSelectionChange('入門')}
        >
          入門
        </button>
      </div>
      <div className={styles.calendarContainer}>
        <FullCalendar
          plugins={[dayGridPlugin, interactionPlugin]}
          initialView="dayGridMonth"
          selectable={true}
          dateClick={handleDateClick}
          events={events.map((event, index) => ({
            title: `第${index + counter}回`,
            start: event.start,
            end: new Date(new Date(event.end).getTime() + 86400000).toISOString().split('T')[0],
            allDay: true,
          }))}
        />
      </div>
      <div className={styles.resultButtonContainer}>
        <div className={styles.cancelButtonContainer}>
          <button onClick={handleCancelSelection}>選択を取り消す</button>
        </div>
        <div className={styles.saveButtonContainer}>
          <button onClick={handleSaveJson}>JSONで保存</button>
        </div>
      </div>
    </div>
  );
};

export default HomePage;

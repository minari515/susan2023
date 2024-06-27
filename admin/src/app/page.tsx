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
  const [events, setEvents] = useState<{ start: string; end: string; index: number}[]>([]);
  const [startDate, setStartDate] = useState<string | null>(null);

  const handleSelectionChange = (value: string) => {
    setSelection(value);
  };

  const handleDateClick = (dateClickInfo: DateClickArg) => {
    const clickedDate = dateClickInfo.dateStr;

    if (startDate) {
      const newEvent = {
        start: startDate,
        end: clickedDate,
        index: (events.length % 8) + 1,
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
        periods: events.map(event => ({
          start: event.start,
          end: event.end,
          index: event.index,
        })),
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
          className={selection === 'Invitation' ? styles.selectedButton : ''}
          onClick={() => handleSelectionChange('Invitation')}
        >
          誘い
        </button>
        <button
          className={selection === 'Introduction' ? styles.selectedButton : ''}
          onClick={() => handleSelectionChange('Introduction')}
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
          events={events.map((event) => ({
            title: `第${event.index}回`,
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
